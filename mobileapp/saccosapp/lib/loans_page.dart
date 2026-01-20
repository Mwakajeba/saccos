import 'package:flutter/material.dart';
import 'loan_application_page.dart';
import 'loan_document_page.dart';
import 'home_page.dart';
import 'profile_page.dart';
import 'loan_repayments_page.dart';
import 'complain_page.dart';
import 'models/user_session.dart';
import 'services/api_service.dart';

class LoansPage extends StatefulWidget {
  const LoansPage({super.key});

  @override
  State<LoansPage> createState() => _LoansPageState();
}

class _LoansPageState extends State<LoansPage> {
  int _selectedTab = 0;
  int _selectedNavIndex = 1;
  bool _isLoading = false;

  String _formatCurrency(double amount) {
    return amount.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]},',
    );
  }

  Future<void> _refreshLoans() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final userId = UserSession.instance.userId;
      if (userId == null) {
        setState(() {
          _isLoading = false;
        });
        return;
      }

      final response = await ApiService.getLoans(userId);
      if (response['status'] == 200) {
        final loans = response['loans'] ?? [];
        UserSession.instance.loans = loans;
        setState(() {
          _isLoading = false;
        });
      } else {
        setState(() {
          _isLoading = false;
        });
      }
    } catch (e) {
      print('Error refreshing loans: $e');
      setState(() {
        _isLoading = false;
      });
    }
  }

  List<dynamic> _getFilteredLoans() {
    final loans = UserSession.instance.loans;
    if (loans == null || loans.isEmpty) {
      print('=== NO LOANS IN SESSION ===');
      print('Loans is null: ${loans == null}');
      print('Loans length: ${loans?.length ?? 0}');
      return [];
    }
    
    print('=== FILTERING LOANS ===');
    print('Total loans: ${loans.length}');
    print('Selected tab: $_selectedTab');
    
    // Print all loan statuses for debugging
    for (var i = 0; i < loans.length; i++) {
      print('Loan $i status: ${loans[i]['status']}');
    }
    
    if (_selectedTab == 1) {
      // Historia - show completed/rejected loans
      // Handle both 'complete' and 'completed' status
      final filtered = loans.where((loan) {
        final status = (loan['status'] as String?)?.toLowerCase() ?? '';
        return status == 'complete' || 
               status == 'completed' || 
               status == 'rejected';
      }).toList();
      print('Historia loans: ${filtered.length}');
      return filtered;
    }
    
    // Orodha Yote - show active/ongoing loans (not completed/rejected)
    final filtered = loans.where((loan) {
      final status = (loan['status'] as String?)?.toLowerCase() ?? '';
      return status != 'complete' && 
             status != 'completed' && 
             status != 'rejected';
    }).toList();
    print('Orodha Yote loans: ${filtered.length}');
    return filtered;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F8F6),
      floatingActionButton: Container(
        margin: const EdgeInsets.only(top: 30, bottom: 12),
        width: 48,
        height: 48,
        child: FloatingActionButton(
          onPressed: () {
            Navigator.of(context).push(
              MaterialPageRoute(
                builder: (context) => const LoanApplicationPage(),
              ),
            );
          },
          backgroundColor: const Color(0xFF111813),
          elevation: 8,
          shape: const CircleBorder(),
          child: const Icon(
            Icons.add,
            size: 24,
            color: Colors.white,
          ),
        ),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
      body: SafeArea(
        bottom: false,
        child: Center(
          child: Container(
            constraints: const BoxConstraints(maxWidth: 448),
            decoration: BoxDecoration(
              color: const Color(0xFFF6F8F6),
              border: Border.symmetric(
                vertical: BorderSide(color: Colors.grey.shade300),
              ),
            ),
            child: Stack(
              children: [
                Column(
                  children: [
                    // Top App Bar
                    Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFFF6F8F6).withOpacity(0.95),
                        border: Border(
                          bottom: BorderSide(color: const Color(0xFFDBE6DF)),
                        ),
                      ),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Mikopo Yangu',
                            style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF111813),
                              letterSpacing: -0.3,
                            ),
                          ),
                          Row(
                            children: [
                              IconButton(
                                onPressed: _refreshLoans,
                                icon: _isLoading
                                    ? const SizedBox(
                                        width: 20,
                                        height: 20,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2,
                                          valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF111813)),
                                        ),
                                      )
                                    : const Icon(
                                        Icons.refresh,
                                        color: Color(0xFF111813),
                                      ),
                              ),
                              IconButton(
                                onPressed: () {},
                                icon: const Icon(
                                  Icons.help_outline,
                                  color: Color(0xFF111813),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    // Summary Card
                    _buildSummaryCard(),
                    // Tabs
                    _buildTabs(),
                    // Loan List
                    Expanded(
                      child: _buildLoansList(),
                    ),
                  ],
                ),
                // Bottom Navigation
                Positioned(
                  bottom: 0,
                  left: 0,
                  right: 0,
                  child: _buildBottomNavigation(),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSummaryCard() {
    final loans = UserSession.instance.loans ?? [];
    double totalDebt = 0.0;
    int activeLoans = 0;
    
    for (var loan in loans) {
      if (loan['status'] == 'active' || loan['status'] == 'disbursed') {
        totalDebt += (loan['total_due'] as num?)?.toDouble() ?? 0.0;
        activeLoans++;
      }
    }
    
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 24, 16, 8),
      child: Container(
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: const Color(0xFF111813),
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.2),
              blurRadius: 20,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: Stack(
          children: [
            Positioned(
              top: -40,
              right: -40,
              child: Container(
                width: 128,
                height: 128,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: const Color(0xFF13EC5B).withOpacity(0.2),
                ),
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Jumla ya Deni',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                    color: Color(0xFF9CA3AF),
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'TZS ${_formatCurrency(totalDebt)}',
                  style: const TextStyle(
                    fontSize: 30,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    const Icon(
                      Icons.trending_down,
                      size: 16,
                      color: Color(0xFF13EC5B),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      'Una mikopo ${loans.length} ($activeLoans Inadaiwa)',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        color: Color(0xFF13EC5B),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTabs() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        border: Border(
          bottom: BorderSide(color: const Color(0xFFDBE6DF)),
        ),
      ),
      child: Row(
        children: [
          _buildTab('Orodha Yote', 0),
          _buildTab('Historia', 1),
        ],
      ),
    );
  }

  Widget _buildTab(String label, int index) {
    final isSelected = _selectedTab == index;
    return Expanded(
      child: GestureDetector(
        onTap: () {
          setState(() {
            _selectedTab = index;
          });
        },
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            border: Border(
              bottom: BorderSide(
                color: isSelected ? const Color(0xFF13EC5B) : Colors.transparent,
                width: 3,
              ),
            ),
          ),
          child: Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: isSelected ? const Color(0xFF111813) : Colors.grey.shade500,
            ),
          ),
        ),
      ),
    );
  }

  @override
  void initState() {
    super.initState();
    // Refresh loans when page loads
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _refreshLoans();
    });
  }

  Widget _buildLoansList() {
    final loans = _getFilteredLoans();
    
    if (loans.isEmpty) {
      final allLoans = UserSession.instance.loans ?? [];
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.credit_card_off,
                size: 64,
                color: Colors.grey.shade300,
              ),
              const SizedBox(height: 16),
              Text(
                allLoans.isEmpty ? 'Hakuna mikopo' : 'Hakuna mikopo katika kategoria hii',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                  color: Colors.grey.shade600,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                allLoans.isEmpty 
                    ? 'Bonyeza kifungo cha + kuomba mkopo'
                    : 'Jumla ya mikopo: ${allLoans.length}',
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey.shade500,
                ),
              ),
              if (allLoans.isNotEmpty) ...[
                const SizedBox(height: 16),
                ElevatedButton.icon(
                  onPressed: _refreshLoans,
                  icon: const Icon(Icons.refresh),
                  label: const Text('Onyesha Yote'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF13EC5B),
                    foregroundColor: const Color(0xFF052E16),
                  ),
                ),
              ],
            ],
          ),
        ),
      );
    }
    
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
      itemCount: loans.length,
      itemBuilder: (context, index) {
        final loan = loans[index];
        
        // Safely convert values to double, handling both string and number types
        double parseValue(dynamic value) {
          if (value is num) return value.toDouble();
          if (value is String) return double.tryParse(value) ?? 0.0;
          return 0.0;
        }
        
        final totalAmount = parseValue(loan['total_amount']);
        final totalRepaid = parseValue(loan['total_repaid']);
        final totalDue = parseValue(loan['total_due']);
        final progress = totalAmount > 0 ? totalRepaid / totalAmount : 0.0;
        
        String status = 'Inaendelea';
        Color statusColor = Colors.green;
        String buttonText = 'Lipa Sasa';
        Color buttonColor = const Color(0xFF13EC5B);
        Color? buttonTextColor;
        bool isCompleted = false;
        
        final loanStatus = (loan['status'] as String?)?.toLowerCase() ?? '';
        switch (loanStatus) {
          case 'pending':
          case 'applied':
            status = 'Imeombwa';
            statusColor = Colors.orange;
            buttonText = 'Angalia Hali';
            buttonColor = Colors.grey.shade200;
            buttonTextColor = Colors.grey.shade700;
            break;
          case 'approved':
          case 'checked':
          case 'authorized':
            status = 'Imeidhinishwa';
            statusColor = Colors.blue;
            buttonText = 'Angalia Hali';
            buttonColor = Colors.blue.shade100;
            buttonTextColor = Colors.blue.shade700;
            break;
          case 'rejected':
            status = 'Imekataliwa';
            statusColor = Colors.red;
            buttonText = 'Cheti';
            buttonColor = Colors.white;
            buttonTextColor = const Color(0xFF111813);
            isCompleted = true;
            break;
          case 'complete':
          case 'completed':
            status = 'Imeisha';
            statusColor = Colors.grey;
            buttonText = 'Cheti';
            buttonColor = Colors.white;
            buttonTextColor = const Color(0xFF111813);
            isCompleted = true;
            break;
          case 'active':
          case 'disbursed':
          default:
            status = 'Inaendelea';
        }
        
        return Padding(
          padding: const EdgeInsets.only(bottom: 16),
          child: _buildLoanCard(
            loanData: loan,
            title: loan['product_name'] ?? 'Mkopo',
            id: '#${loan['loan_no'] ?? ''}',
            amount: _formatCurrency(totalAmount),
            balance: isCompleted ? 'Imelipwa' : _formatCurrency(totalDue),
            progress: isCompleted ? 1.0 : progress,
            status: status,
            statusColor: statusColor,
            icon: Icons.credit_card,
            iconBgColor: statusColor.withOpacity(0.1),
            iconColor: statusColor,
            dueDate: loan['last_repayment_date'] ?? 'Inasubiriwa',
            buttonText: buttonText,
            buttonColor: buttonColor,
            buttonTextColor: buttonTextColor,
            isCompleted: isCompleted,
          ),
        );
      },
    );
  }

  Widget _buildLoanCard({
    required Map<String, dynamic> loanData,
    required String title,
    required String id,
    required String amount,
    required String balance,
    required double? progress,
    required String status,
    required Color statusColor,
    required IconData icon,
    required Color iconBgColor,
    required Color iconColor,
    required String dueDate,
    required String buttonText,
    required Color buttonColor,
    Color? buttonTextColor,
    bool isCompleted = false,
  }) {
    final loanStatus = (loanData['status'] as String?)?.toLowerCase() ?? '';
    final showKycButton = loanStatus == 'applied' || loanStatus == 'pending';

    return GestureDetector(
      onTap: () {
        if (status == 'Inaendelea' || status == 'Imeisha') {
          // Pass entire loan data
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => LoanRepaymentsPage(
                loanData: loanData,
              ),
            ),
          );
        }
      },
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFFDBE6DF)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
          children: [
            // Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        color: iconBgColor,
                        shape: BoxShape.circle,
                      ),
                      child: Icon(icon, color: iconColor, size: 20),
                    ),
                    const SizedBox(width: 12),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          title,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF111813),
                          ),
                        ),
                        Text(
                          'ID: $id',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                            color: Colors.grey.shade500,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: statusColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(100),
                  ),
                  child: Text(
                    status,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: statusColor,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            // Amount Details
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'KIASI CHA MKOPO',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: Colors.grey.shade500,
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'TZS $amount',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF111813),
                        ),
                      ),
                    ],
                  ),
                ),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'SALIO LILILOBAKI',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: Colors.grey.shade500,
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        balance.startsWith('TZS') ? balance : balance == '--' ? '--' : 'TZS $balance',
                        style: TextStyle(
                          fontSize: balance == 'Imelipwa' ? 14 : 18,
                          fontWeight: FontWeight.w700,
                          color: balance == 'Imelipwa' ? Colors.green : const Color(0xFF13EC5B),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            if (progress != null) ...[
              const SizedBox(height: 16),
              Column(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(100),
                    child: LinearProgressIndicator(
                      value: progress,
                      minHeight: 6,
                      backgroundColor: const Color(0xFFDBE6DF),
                      valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF13EC5B)),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Align(
                    alignment: Alignment.centerLeft,
                    child: Text(
                      'Imelipwa ${(progress * 100).toInt()}%',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ),
                ],
              ),
            ],
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.only(top: 12),
              decoration: BoxDecoration(
                border: Border(
                  top: BorderSide(color: const Color(0xFFDBE6DF)),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isCompleted ? 'Tarehe ya Kumaliza' : 'Tarehe ya Mwisho',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w500,
                          color: Colors.grey.shade500,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Icon(
                            isCompleted ? Icons.event_available : Icons.calendar_today,
                            size: 14,
                            color: Colors.grey.shade400,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            dueDate,
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                              color: dueDate == 'Inasubiriwa' ? Colors.grey.shade500 : const Color(0xFF111813),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  ElevatedButton(
                    onPressed: () {
                      if (buttonText == 'Lipa Sasa') {
                        _showPaymentModal(context);
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: buttonColor,
                      foregroundColor: buttonTextColor ?? const Color(0xFF102216),
                      elevation: buttonColor == Colors.white ? 0 : 2,
                      side: buttonColor == Colors.white
                          ? BorderSide(color: Colors.grey.shade200)
                          : null,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    ),
                    child: Text(
                      buttonText,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            if (showKycButton) ...[
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => LoanDocumentPage(loanData: loanData),
                      ),
                    );
                  },
                  icon: const Icon(Icons.verified_user_outlined),
                  label: const Text('Weka/Ona KYC'),
                ),
              ),
            ],
          ],
        ),
        ),
      ),
    );
  }

  void _showPaymentModal(BuildContext context) {
    final TextEditingController amountController = TextEditingController();
    final TextEditingController phoneController = TextEditingController();
    DateTime selectedDate = DateTime.now();
    String? receiptFileName;
    bool isManual = true; // Kawaida = true, Automatiki = false
    String selectedNetwork = 'M-PESA';
    final networks = ['M-PESA', 'TIGO PESA', 'HALOPESA', 'AIRTEL MONEY', 'T-PESA'];

    showDialog(
      context: context,
      builder: (BuildContext context) {
        return StatefulBuilder(
          builder: (BuildContext context, StateSetter setModalState) {
            return Dialog(
              backgroundColor: Colors.transparent,
              insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 40),
              child: Container(
                height: MediaQuery.of(context).size.height * 0.5,
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Column(
                  children: [
                    // Header
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Lipa Mkopo',
                            style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF111813),
                            ),
                          ),
                          IconButton(
                            onPressed: () => Navigator.pop(context),
                            icon: const Icon(Icons.close),
                          ),
                        ],
                      ),
                    ),
                    const Divider(height: 1),
                    // Content
                    Expanded(
                      child: SingleChildScrollView(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Payment Mode Switch
                            Container(
                              padding: const EdgeInsets.all(4),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF0F4F2),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Row(
                                children: [
                                  Expanded(
                                    child: GestureDetector(
                                      onTap: () {
                                        setModalState(() {
                                          isManual = true;
                                        });
                                      },
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                        decoration: BoxDecoration(
                                          color: isManual ? Colors.white : Colors.transparent,
                                          borderRadius: BorderRadius.circular(8),
                                          boxShadow: isManual
                                              ? [
                                                  BoxShadow(
                                                    color: Colors.black.withOpacity(0.05),
                                                    blurRadius: 4,
                                                    offset: const Offset(0, 2),
                                                  ),
                                                ]
                                              : null,
                                        ),
                                        child: const Text(
                                          'Kawaida',
                                          textAlign: TextAlign.center,
                                          style: TextStyle(
                                            fontSize: 14,
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                      ),
                                    ),
                                  ),
                                  Expanded(
                                    child: GestureDetector(
                                      onTap: () {
                                        setModalState(() {
                                          isManual = false;
                                        });
                                      },
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                        decoration: BoxDecoration(
                                          color: !isManual ? Colors.white : Colors.transparent,
                                          borderRadius: BorderRadius.circular(8),
                                          boxShadow: !isManual
                                              ? [
                                                  BoxShadow(
                                                    color: Colors.black.withOpacity(0.05),
                                                    blurRadius: 4,
                                                    offset: const Offset(0, 2),
                                                  ),
                                                ]
                                              : null,
                                        ),
                                        child: const Text(
                                          'Automatiki',
                                          textAlign: TextAlign.center,
                                          style: TextStyle(
                                            fontSize: 14,
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 20),
                            
                            // Conditional Content based on payment mode
                            if (isManual) ...[
                              // KAWAIDA MODE - Amount, Date, Receipt Upload
                              const Text(
                                'Kiasi cha Malipo',
                                style: TextStyle(
                                  fontSize: 16,
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
                              border: Border.all(color: const Color(0xFFDBE6DF)),
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
                                    controller: amountController,
                                    keyboardType: TextInputType.number,
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
                          const SizedBox(height: 24),
                          // Date Picker
                          const Text(
                            'Tarehe ya Malipo',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF111813),
                            ),
                          ),
                          const SizedBox(height: 12),
                          GestureDetector(
                            onTap: () async {
                              final DateTime? picked = await showDatePicker(
                                context: context,
                                initialDate: selectedDate,
                                firstDate: DateTime(2020),
                                lastDate: DateTime.now(),
                                builder: (context, child) {
                                  return Theme(
                                    data: Theme.of(context).copyWith(
                                      colorScheme: const ColorScheme.light(
                                        primary: Color(0xFF13EC5B),
                                      ),
                                    ),
                                    child: child!,
                                  );
                                },
                              );
                              if (picked != null) {
                                setModalState(() {
                                  selectedDate = picked;
                                });
                              }
                            },
                            child: Container(
                              height: 56,
                              padding: const EdgeInsets.symmetric(horizontal: 16),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: const Color(0xFFDBE6DF)),
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Row(
                                    children: [
                                      const Icon(
                                        Icons.calendar_today,
                                        size: 20,
                                        color: Color(0xFF13EC5B),
                                      ),
                                      const SizedBox(width: 12),
                                      Text(
                                        '${selectedDate.day}/${selectedDate.month}/${selectedDate.year}',
                                        style: const TextStyle(
                                          fontSize: 16,
                                          fontWeight: FontWeight.w600,
                                          color: Color(0xFF111813),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const Icon(
                                    Icons.arrow_drop_down,
                                    color: Color(0xFF9CA3AF),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(height: 24),
                          // Receipt Upload
                          const Text(
                            'Pakia Risiti',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF111813),
                            ),
                          ),
                          const SizedBox(height: 12),
                          GestureDetector(
                            onTap: () {
                              setModalState(() {
                                receiptFileName = 'risiti_${DateTime.now().millisecondsSinceEpoch}.jpg';
                              });
                            },
                            child: Container(
                              padding: const EdgeInsets.all(20),
                              decoration: BoxDecoration(
                                color: const Color(0xFF13EC5B).withOpacity(0.05),
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: const Color(0xFF13EC5B).withOpacity(0.3),
                                  style: BorderStyle.solid,
                                  width: 2,
                                ),
                              ),
                              child: Column(
                                children: [
                                  Container(
                                    width: 60,
                                    height: 60,
                                    decoration: BoxDecoration(
                                      color: const Color(0xFF13EC5B).withOpacity(0.1),
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(
                                      Icons.upload_file,
                                      size: 30,
                                      color: Color(0xFF13EC5B),
                                    ),
                                  ),
                                  const SizedBox(height: 12),
                                  Text(
                                    receiptFileName ?? 'Bonyeza kupakia risiti',
                                    style: TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w600,
                                      color: receiptFileName != null
                                          ? const Color(0xFF13EC5B)
                                          : const Color(0xFF6B7280),
                                    ),
                                  ),
                                  if (receiptFileName == null)
                                    const SizedBox(height: 4),
                                  if (receiptFileName == null)
                                    Text(
                                      'PDF, JPG au PNG (Max 5MB)',
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: Colors.grey.shade500,
                                      ),
                                    ),
                                  if (receiptFileName != null)
                                    const SizedBox(height: 8),
                                  if (receiptFileName != null)
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        const Icon(
                                          Icons.check_circle,
                                          size: 16,
                                          color: Color(0xFF13EC5B),
                                        ),
                                        const SizedBox(width: 4),
                                        Text(
                                          'Imepakiwa',
                                          style: TextStyle(
                                            fontSize: 12,
                                            fontWeight: FontWeight.w600,
                                            color: const Color(0xFF13EC5B),
                                          ),
                                        ),
                                      ],
                                    ),
                                ],
                              ),
                            ),
                          ),
                            ] else ...[
                              // AUTOMATIKI MODE - Network, Phone, Amount
                              const Text(
                                'Chagua Mtandao',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w700,
                                  color: Color(0xFF111813),
                                ),
                              ),
                              const SizedBox(height: 12),
                              Container(
                                height: 56,
                                padding: const EdgeInsets.symmetric(horizontal: 16),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: const Color(0xFFDBE6DF)),
                                ),
                                child: DropdownButton<String>(
                                  value: selectedNetwork,
                                  isExpanded: true,
                                  underline: const SizedBox(),
                                  icon: const Icon(Icons.arrow_drop_down, color: Color(0xFF9CA3AF)),
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF111813),
                                  ),
                                  items: networks.map((String network) {
                                    return DropdownMenuItem<String>(
                                      value: network,
                                      child: Text(network),
                                    );
                                  }).toList(),
                                  onChanged: (String? newValue) {
                                    if (newValue != null) {
                                      setModalState(() {
                                        selectedNetwork = newValue;
                                      });
                                    }
                                  },
                                ),
                              ),
                              const SizedBox(height: 24),
                              const Text(
                                'Namba ya Simu',
                                style: TextStyle(
                                  fontSize: 16,
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
                                  border: Border.all(color: const Color(0xFFDBE6DF)),
                                ),
                                child: Row(
                                  children: [
                                    const Padding(
                                      padding: EdgeInsets.only(left: 16),
                                      child: Text(
                                        '+255',
                                        style: TextStyle(
                                          fontSize: 16,
                                          fontWeight: FontWeight.w700,
                                          color: Color(0xFF9CA3AF),
                                        ),
                                      ),
                                    ),
                                    Expanded(
                                      child: TextField(
                                        controller: phoneController,
                                        keyboardType: TextInputType.phone,
                                        decoration: const InputDecoration(
                                          border: InputBorder.none,
                                          contentPadding: EdgeInsets.symmetric(horizontal: 16),
                                          hintText: '7XX XXX XXX',
                                          hintStyle: TextStyle(
                                            color: Color(0xFFD1D5DB),
                                            fontSize: 16,
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                        style: const TextStyle(
                                          fontSize: 16,
                                          fontWeight: FontWeight.w600,
                                          color: Color(0xFF111813),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 24),
                              const Text(
                                'Kiasi cha Malipo',
                                style: TextStyle(
                                  fontSize: 16,
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
                                  border: Border.all(color: const Color(0xFFDBE6DF)),
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
                                        controller: amountController,
                                        keyboardType: TextInputType.number,
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
                            ],
                          ],
                        ),
                      ),
                    ),
                    // Bottom Button
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        border: Border(
                          top: BorderSide(color: Colors.grey.shade200),
                        ),
                      ),
                      child: SafeArea(
                        top: false,
                        child: SizedBox(
                          width: double.infinity,
                          height: 50,
                          child: ElevatedButton(
                            onPressed: () {
                              // Handle payment submission
                              Navigator.pop(context);
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text('Malipo yamepokelewa!'),
                                  backgroundColor: Color(0xFF13EC5B),
                                ),
                              );
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
                            child: const Text(
                              'Tuma Malipo',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildBottomNavigation() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(
          top: BorderSide(color: Colors.grey.shade200),
        ),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildNavItem(Icons.home, 'Nyumbani', 0),
              _buildNavItem(Icons.credit_score_outlined, 'Mikopo', 1),
              const SizedBox(width: 56), // Space for FAB
              _buildNavItem(Icons.feedback_outlined, 'Malalamiko', 2),
              _buildNavItem(Icons.person_outline, 'Wasifu', 3),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem(IconData icon, String label, int index) {
    final isSelected = _selectedNavIndex == index;
    return GestureDetector(
      onTap: () {
        if (index == 0) {
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(
              builder: (context) => const HomePage(),
            ),
          );
        } else if (index == 2) {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (context) => const ComplainPage(),
            ),
          );
        } else if (index == 3) {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (context) => const ProfilePage(),
            ),
          );
        }
      },
      child: Container(
        width: 64,
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 24,
              color: isSelected
                  ? const Color(0xFF13EC5B)
                  : const Color(0xFF9CA3AF),
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 10,
                fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                color: isSelected
                    ? const Color(0xFF13EC5B)
                    : const Color(0xFF9CA3AF),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
