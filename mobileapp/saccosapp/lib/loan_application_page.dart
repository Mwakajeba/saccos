import 'package:flutter/material.dart';
import 'services/api_service.dart';

class LoanApplicationPage extends StatefulWidget {
  const LoanApplicationPage({super.key});

  @override
  State<LoanApplicationPage> createState() => _LoanApplicationPageState();
}

class _LoanApplicationPageState extends State<LoanApplicationPage> {
  final TextEditingController _amountController = TextEditingController(text: '500000');
  int _selectedDuration = 6;
  String? _selectedPurpose;
  List<dynamic> _products = [];
  bool _isLoadingProducts = false;
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
        // Handle error
      } finally {
        setState(() { _isLoadingProducts = false; });
      }
    }
  String? _selectedLoanPurpose;

  @override
  void dispose() {
    _amountController.dispose();
    super.dispose();
  }

  double _calculateInterest() {
    final amount = double.tryParse(_amountController.text) ?? 0;
    return amount * 0.10;
  }

  double _calculateTotal() {
    final amount = double.tryParse(_amountController.text) ?? 0;
    return amount + _calculateInterest();
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
                        // Loan Purpose
                        _buildLoanPurposeSection(),
                        const SizedBox(height: 24),
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
                        onPressed: () {
                          // Handle submit
                          Navigator.of(context).pop();
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF13EC5B),
                          foregroundColor: const Color(0xFF052E16),
                          elevation: 8,
                          shadowColor: const Color(0xFF13EC5B).withOpacity(0.25),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: const Row(
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
        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 8),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Kiwango cha chini: 50,000',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF9CA3AF),
                ),
              ),
              Text(
                'Kiwango cha juu: 5,000,000',
                style: TextStyle(
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
                    if (_selectedDuration > 1) {
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
                    if (_selectedDuration < 36) {
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
                  onChanged: (value) {
                    setState(() {
                      _selectedPurpose = value;
                    });
                  },
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
              DropdownMenuItem(value: 'business', child: Text('Biashara ndogo')),
              DropdownMenuItem(value: 'school', child: Text('Ada ya Shule')),
              DropdownMenuItem(value: 'medical', child: Text('Matibabu')),
              DropdownMenuItem(value: 'agri', child: Text('Kilimo')),
              DropdownMenuItem(value: 'emergency', child: Text('Dharura')),
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

  Widget _buildSummaryCard() {
    final interest = _calculateInterest();
    final total = _calculateTotal();

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
              const Text(
                'Riba (10%)',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF6B7280),
                ),
              ),
              Text(
                'TZS ${interest.toStringAsFixed(0)}',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF111813),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          const Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Tarehe ya kurejesha',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF6B7280),
                ),
              ),
              Text(
                '24 Okt 2024',
                style: TextStyle(
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
                'TZS ${total.toStringAsFixed(0)}',
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
