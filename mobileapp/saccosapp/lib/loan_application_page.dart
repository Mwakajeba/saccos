import 'package:flutter/material.dart';
import 'services/api_service.dart';
import 'models/user_session.dart';

class LoanApplicationPage extends StatefulWidget {
  const LoanApplicationPage({super.key});

  @override
  State<LoanApplicationPage> createState() => _LoanApplicationPageState();
}

class _LoanApplicationPageState extends State<LoanApplicationPage> {
  final TextEditingController _amountController = TextEditingController();
  final TextEditingController _interestController = TextEditingController();
  int _selectedDuration = 6;
  String? _selectedPurpose;
  String? _selectedInterestCycle = 'monthly';
  List<dynamic> _products = [];
  Map<String, dynamic>? _selectedProduct;
  bool _isLoadingProducts = false;
  bool _isSubmitting = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _fetchProducts();
  }

  Future<void> _fetchProducts() async {
    setState(() { _isLoadingProducts = true; });
    try {
      final response = await ApiService.getLoanProducts();
      if (response['status'] == 200 && response['products'] != null) {
        setState(() {
          _products = response['products'];
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Imeshindwa kupata bidhaa za mkopo';
      });
    } finally {
      setState(() { _isLoadingProducts = false; });
    }
  }

  void _onProductSelected(String? productId) {
    setState(() {
      _selectedPurpose = productId;
      if (productId != null) {
        _selectedProduct = _products.firstWhere(
          (p) => p['id'].toString() == productId,
          orElse: () => null,
        );
        if (_selectedProduct != null) {
          // Debug: Print product data
          print('=== SELECTED PRODUCT ===');
          print('Product: ${_selectedProduct}');
          print('min_amount: ${_selectedProduct!['min_amount']}');
          print('max_amount: ${_selectedProduct!['max_amount']}');
          print('min_interest_rate: ${_selectedProduct!['min_interest_rate']}');
          print('max_interest_rate: ${_selectedProduct!['max_interest_rate']}');
          print('min_period: ${_selectedProduct!['min_period']}');
          print('max_period: ${_selectedProduct!['max_period']}');
          print('========================');
          
          // Set default interest rate (1 decimal place)
          final defaultInterest = _selectedProduct!['default_interest_rate'];
          final minInterest = _selectedProduct!['min_interest_rate'];
          final interestToUse = defaultInterest ?? minInterest ?? 0;
          _interestController.text = _toDouble(interestToUse).toStringAsFixed(1);
          
          // Set default amount to minimum
          final minAmount = _selectedProduct!['min_amount'];
          _amountController.text = (minAmount != null) ? minAmount.toString() : '0';
          
          // Set default period to minimum
          final minPeriod = _selectedProduct!['min_period'];
          _selectedDuration = _toInt(minPeriod ?? 6);
        }
      } else {
        _selectedProduct = null;
      }
    });
  }

  String? _selectedLoanPurpose;

  @override
  void dispose() {
    _amountController.dispose();
    _interestController.dispose();
    super.dispose();
  }

  double _getSelectedAmount() {
    return double.tryParse(_amountController.text) ?? 0;
  }

  double _getSelectedInterestRate() {
    return double.tryParse(_interestController.text) ?? 0;
  }

  String _formatCurrency(double amount) {
    return amount.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]},',
    );
  }

  double _toDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  int _toInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is double) return value.toInt();
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  Future<void> _submitApplication() async {
    // Validation
    if (_selectedPurpose == null) {
      _showError('Tafadhali chagua bidhaa ya mkopo');
      return;
    }

    // Check if product allows app applications
    if (_selectedProduct != null) {
      final allowedInAppValue = _selectedProduct!['allowed_in_app'];
      // Handle both int (0/1) and bool values from API
      final allowedInApp = allowedInAppValue is bool 
          ? allowedInAppValue 
          : (allowedInAppValue is int 
              ? allowedInAppValue == 1 
              : (allowedInAppValue == true || allowedInAppValue == '1'));
      
      if (!allowedInApp) {
        _showError('Dirisha la maombi kwenye hii bidhaa limefungwa');
        return;
      }
    }

    if (_selectedLoanPurpose == null) {
      _showError('Tafadhali chagua kusudi la mkopo');
      return;
    }

    final amount = _getSelectedAmount();
    if (amount <= 0) {
      _showError('Tafadhali weka kiasi cha mkopo');
      return;
    }

    final interestRate = _getSelectedInterestRate();
    if (interestRate <= 0) {
      _showError('Tafadhali weka kiwango cha riba');
      return;
    }

    if (_selectedProduct != null) {
      final minAmount = _toDouble(_selectedProduct!['min_amount']);
      final maxAmount = _toDouble(_selectedProduct!['max_amount']);
      final minInterest = _toDouble(_selectedProduct!['min_interest_rate']);
      final maxInterest = _toDouble(_selectedProduct!['max_interest_rate']);
      final minPeriod = _toInt(_selectedProduct!['min_period']);
      final maxPeriod = _toInt(_selectedProduct!['max_period']);

      // Validate against product limits
      if (amount < minAmount || amount > maxAmount) {
        _showError('Kiasi lazima kiwe kati ya ${_formatCurrency(minAmount)} na ${_formatCurrency(maxAmount)}');
        return;
      }

      if (interestRate < minInterest || interestRate > maxInterest) {
        _showError('Kiwango cha riba lazima kiwe kati ya ${minInterest.toStringAsFixed(1)}% na ${maxInterest.toStringAsFixed(1)}%');
        return;
      }

      if (_selectedDuration < minPeriod || _selectedDuration > maxPeriod) {
        _showError('Muda lazima uwe kati ya $minPeriod na $maxPeriod miezi');
        return;
      }
    }

    final userSession = UserSession.instance;
    if (userSession.userId == null) {
      _showError('Tafadhali ingia tena');
      return;
    }

    setState(() {
      _isSubmitting = true;
      _errorMessage = null;
    });

    try {
      final applicationData = {
        'product_id': int.parse(_selectedPurpose!),
        'period': _selectedDuration,
        'interest': interestRate,
        'amount': amount,
        'date_applied': DateTime.now().toIso8601String().split('T')[0],
        'customer_id': userSession.userId,
        'group_id': userSession.groupId,
        'sector': _mapSectorToEnglish(_selectedLoanPurpose!),
        'interest_cycle': _selectedInterestCycle,
      };

      final response = await ApiService.submitLoanApplication(applicationData);

      if (response['status'] == 200) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Ombi la mkopo limewasilishwa kwa mafanikio'),
              backgroundColor: Colors.green,
            ),
          );
          Navigator.of(context).pop(true); // Return true to indicate success
        }
      }
    } catch (e) {
      String errorMsg = 'Imeshindwa kuwasilisha ombi la mkopo';
      if (e.toString().contains('Exception:')) {
        errorMsg = e.toString().replaceFirst('Exception:', '').trim();
      }
      _showError(errorMsg);
    } finally {
      if (mounted) {
        setState(() {
          _isSubmitting = false;
        });
      }
    }
  }

  String _mapSectorToEnglish(String value) {
    // Value is already in English format from dropdown
    return value;
  }

  void _showError(String message) {
    setState(() {
      _errorMessage = message;
    });
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F8F6),
      body: SafeArea(
        child: Center(
          child: Container(
            constraints: const BoxConstraints(maxWidth: 448),
            decoration: BoxDecoration(
              color: const Color(0xFFF6F8F6),
              border: Border.symmetric(
                vertical: BorderSide(color: Colors.grey.shade300),
              ),
            ),
            child: Column(
              children: [
                // Top App Bar
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    border: Border(
                      bottom: BorderSide(color: Colors.grey.shade100),
                    ),
                  ),
                  padding: const EdgeInsets.fromLTRB(4, 8, 16, 8),
                  child: Row(
                    children: [
                      IconButton(
                        onPressed: () => Navigator.of(context).pop(),
                        icon: const Icon(
                          Icons.arrow_back,
                          color: Color(0xFF111813),
                        ),
                      ),
                      const Expanded(
                        child: Text(
                          'Omba Mkopo',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF111813),
                            letterSpacing: -0.3,
                          ),
                        ),
                      ),
                      const SizedBox(width: 40),
                    ],
                  ),
                ),
                // Scrollable Content
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Purpose Selection (First)
                        _buildPurposeSection(),
                        const SizedBox(height: 24),
                        // Amount Section (Second)
                        _buildAmountSection(),
                        const SizedBox(height: 24),
                        // Duration Selection
                        _buildDurationSection(),
                        const SizedBox(height: 24),
                        // Interest Rate Section
                        _buildInterestRateSection(),
                        const SizedBox(height: 24),
                        // Interest Cycle Section
                        _buildInterestCycleSection(),
                        const SizedBox(height: 24),
                        // Loan Purpose
                        _buildLoanPurposeSection(),
                        const SizedBox(height: 24),
                        // Error Message
                        if (_errorMessage != null)
                          Container(
                            padding: const EdgeInsets.all(12),
                            margin: const EdgeInsets.only(bottom: 16),
                            decoration: BoxDecoration(
                              color: Colors.red.shade50,
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: Colors.red.shade200),
                            ),
                            child: Row(
                              children: [
                                Icon(Icons.error_outline, color: Colors.red.shade700, size: 20),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    _errorMessage!,
                                    style: TextStyle(
                                      color: Colors.red.shade700,
                                      fontSize: 14,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        // Summary Card
                        _buildSummaryCard(),
                        const SizedBox(height: 100),
                      ],
                    ),
                  ),
                ),
                // Bottom Action Button
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    border: Border(
                      top: BorderSide(color: Colors.grey.shade100),
                    ),
                  ),
                  padding: const EdgeInsets.all(16),
                  child: SafeArea(
                    top: false,
                      child: SizedBox(
                      width: double.infinity,
                      height: 50,
                      child: ElevatedButton(
                        onPressed: _isSubmitting ? null : _submitApplication,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF13EC5B),
                          foregroundColor: const Color(0xFF052E16),
                          elevation: 8,
                          shadowColor: const Color(0xFF13EC5B).withOpacity(0.25),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: _isSubmitting
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF052E16)),
                                ),
                              )
                            : const Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(
                                    'Tuma Ombi',
                                    style: TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.w700,
                                    ),
                                  ),
                                  SizedBox(width: 8),
                                  Icon(Icons.arrow_forward, size: 20),
                                ],
                              ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildAmountSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Kiasi unachohitaji',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: Color(0xFF111813),
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'Weka kiasi unachotaka kukopa hapa chini.',
          style: TextStyle(
            fontSize: 14,
            color: Color(0xFF6B7280),
          ),
        ),
        const SizedBox(height: 16),
        Container(
          height: 56,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 10,
              ),
            ],
          ),
          child: Row(
            children: [
              const Padding(
                padding: EdgeInsets.only(left: 16),
                child: Text(
                  'TZS',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF9CA3AF),
                  ),
                ),
              ),
              Expanded(
                child: TextField(
                  controller: _amountController,
                  keyboardType: TextInputType.number,
                  onChanged: (value) => setState(() {}),
                  decoration: const InputDecoration(
                    border: InputBorder.none,
                    contentPadding: EdgeInsets.symmetric(horizontal: 16),
                    hintText: '0',
                    hintStyle: TextStyle(
                      color: Color(0xFFD1D5DB),
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF111813),
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                _selectedProduct != null
                    ? 'Kiwango cha chini: ${_formatCurrency(_toDouble(_selectedProduct!['min_amount']))}'
                    : 'Kiwango cha chini: -',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF9CA3AF),
                ),
              ),
              Text(
                _selectedProduct != null
                    ? 'Kiwango cha juu: ${_formatCurrency(_toDouble(_selectedProduct!['max_amount']))}'
                    : 'Kiwango cha juu: -',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF13EC5B),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildDurationSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Muda wa Kurejesha',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: Color(0xFF111813),
          ),
        ),
        const SizedBox(height: 12),
        Container(
          height: 56,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 4,
              ),
            ],
          ),
          child: Row(
            children: [
              // Minus Button
              IconButton(
                onPressed: () {
                  setState(() {
                    final minPeriod = _toInt(_selectedProduct?['min_period'] ?? 1);
                    if (_selectedDuration > minPeriod) {
                      _selectedDuration--;
                    }
                  });
                },
                icon: Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: const Color(0xFF13EC5B).withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.remove,
                    color: Color(0xFF13EC5B),
                    size: 20,
                  ),
                ),
              ),
              // Display
              Expanded(
                child: Center(
                  child: Text(
                    '$_selectedDuration ${_selectedDuration == 1 ? 'Mwezi' : 'Miezi'}',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF111813),
                    ),
                  ),
                ),
              ),
              // Plus Button
              IconButton(
                onPressed: () {
                  setState(() {
                    final maxPeriod = _toInt(_selectedProduct?['max_period'] ?? 36);
                    if (_selectedDuration < maxPeriod) {
                      _selectedDuration++;
                    }
                  });
                },
                icon: Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: const Color(0xFF13EC5B).withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.add,
                    color: Color(0xFF13EC5B),
                    size: 20,
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildPurposeSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Chagua Bidhaa',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: Color(0xFF111813),
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'Chagua aina ya mkopo unaohitaji.',
          style: TextStyle(
            fontSize: 14,
            color: Color(0xFF6B7280),
          ),
        ),
        const SizedBox(height: 16),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 4,
              ),
            ],
          ),
          child: _isLoadingProducts
              ? const Padding(
                  padding: EdgeInsets.all(16),
                  child: Center(child: CircularProgressIndicator()),
                )
              : DropdownButtonFormField<String>(
                  value: _selectedPurpose,
                  decoration: const InputDecoration(
                    border: InputBorder.none,
                    contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                    hintText: 'Chagua bidhaa...',
                    hintStyle: TextStyle(color: Color(0xFF9CA3AF)),
                  ),
                  icon: const Icon(Icons.expand_more, color: Color(0xFF9CA3AF)),
                  style: const TextStyle(
                    fontSize: 16,
                    color: Color(0xFF111813),
                  ),
                  dropdownColor: Colors.white,
                  items: _products.map<DropdownMenuItem<String>>((product) {
                    return DropdownMenuItem<String>(
                      value: product['id'].toString(),
                      child: Text(product['name'] ?? ''),
                    );
                  }).toList(),
                  onChanged: _onProductSelected,
                ),
        ),
      ],
    );
  }

  Widget _buildLoanPurposeSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Kusudi la Mkopo',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: Color(0xFF111813),
          ),
        ),
        const SizedBox(height: 12),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 4,
              ),
            ],
          ),
          child: DropdownButtonFormField<String>(
            value: _selectedLoanPurpose,
            decoration: const InputDecoration(
              border: InputBorder.none,
              contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 16),
              hintText: 'Chagua sababu...',
              hintStyle: TextStyle(color: Color(0xFF9CA3AF)),
            ),
            icon: const Icon(Icons.expand_more, color: Color(0xFF9CA3AF)),
            style: const TextStyle(
              fontSize: 16,
              color: Color(0xFF111813),
            ),
            dropdownColor: Colors.white,
            items: const [
              DropdownMenuItem(value: 'Business', child: Text('Biashara ndogo')),
              DropdownMenuItem(value: 'Education', child: Text('Ada ya Shule')),
              DropdownMenuItem(value: 'Health', child: Text('Matibabu')),
              DropdownMenuItem(value: 'Agriculture', child: Text('Kilimo')),
              DropdownMenuItem(value: 'Other', child: Text('Dharura')),
            ],
            onChanged: (value) {
              setState(() {
                _selectedLoanPurpose = value;
              });
            },
          ),
        ),
      ],
    );
  }

  Widget _buildInterestRateSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Kiwango cha Riba (%)',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: Color(0xFF111813),
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'Weka kiwango cha riba kwa asilimia.',
          style: TextStyle(
            fontSize: 14,
            color: Color(0xFF6B7280),
          ),
        ),
        const SizedBox(height: 16),
        Container(
          height: 56,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 10,
              ),
            ],
          ),
          child: Row(
            children: [
              const Padding(
                padding: EdgeInsets.only(left: 16),
                child: Text(
                  '%',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF9CA3AF),
                  ),
                ),
              ),
              Expanded(
                child: TextField(
                  controller: _interestController,
                  keyboardType: TextInputType.numberWithOptions(decimal: true),
                  onChanged: (value) => setState(() {}),
                  decoration: const InputDecoration(
                    border: InputBorder.none,
                    contentPadding: EdgeInsets.symmetric(horizontal: 16),
                    hintText: '0',
                    hintStyle: TextStyle(
                      color: Color(0xFFD1D5DB),
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF111813),
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                _selectedProduct != null
                    ? 'Kiwango cha chini: ${_toDouble(_selectedProduct!['min_interest_rate']).toStringAsFixed(1)}%'
                    : 'Kiwango cha chini: -',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF9CA3AF),
                ),
              ),
              Text(
                _selectedProduct != null
                    ? 'Kiwango cha juu: ${_toDouble(_selectedProduct!['max_interest_rate']).toStringAsFixed(1)}%'
                    : 'Kiwango cha juu: -',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF13EC5B),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildInterestCycleSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Mzunguko wa Riba',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: Color(0xFF111813),
          ),
        ),
        const SizedBox(height: 12),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 4,
              ),
            ],
          ),
          child: DropdownButtonFormField<String>(
            value: _selectedInterestCycle,
            decoration: const InputDecoration(
              border: InputBorder.none,
              contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 16),
              hintText: 'Chagua mzunguko...',
              hintStyle: TextStyle(color: Color(0xFF9CA3AF)),
            ),
            icon: const Icon(Icons.expand_more, color: Color(0xFF9CA3AF)),
            style: const TextStyle(
              fontSize: 16,
              color: Color(0xFF111813),
            ),
            dropdownColor: Colors.white,
            items: const [
              DropdownMenuItem(value: 'daily', child: Text('Kila siku')),
              DropdownMenuItem(value: 'weekly', child: Text('Kila wiki')),
              DropdownMenuItem(value: 'monthly', child: Text('Kila mwezi')),
              DropdownMenuItem(value: 'quarterly', child: Text('Kila robo mwaka')),
              DropdownMenuItem(value: 'semi_annually', child: Text('Kila nusu mwaka')),
              DropdownMenuItem(value: 'annually', child: Text('Kila mwaka')),
            ],
            onChanged: (value) {
              setState(() {
                _selectedInterestCycle = value;
              });
            },
          ),
        ),
      ],
    );
  }

  Widget _buildSummaryCard() {
    final amount = _getSelectedAmount();
    final interestRate = _getSelectedInterestRate();
    // Simple interest calculation: (amount * interest_rate * period) / 100
    final interest = (amount * interestRate * _selectedDuration) / 100;
    final total = amount + interest;

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            Colors.white,
            Colors.grey.shade50,
          ],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade100),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.only(bottom: 12),
            decoration: BoxDecoration(
              border: Border(
                bottom: BorderSide(color: Colors.grey.shade100),
              ),
            ),
            child: const Text(
              'MAELEZO YA MAREJESHO',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: Color(0xFF6B7280),
                letterSpacing: 1,
              ),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Riba (${interestRate.toStringAsFixed(1)}%)',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF6B7280),
                ),
              ),
              Text(
                'TZS ${_formatCurrency(interest)}',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF111813),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Muda wa kurejesha',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF6B7280),
                ),
              ),
              Text(
                '$_selectedDuration ${_selectedDuration == 1 ? 'Mwezi' : 'Miezi'}',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF111813),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Container(
            height: 1,
            decoration: BoxDecoration(
              border: Border(
                top: BorderSide(color: Colors.grey.shade200, style: BorderStyle.solid),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              const Text(
                'Jumla ya Marejesho',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF6B7280),
                ),
              ),
              Text(
                'TZS ${_formatCurrency(total)}',
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFF13EC5B),
                  letterSpacing: -0.5,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
