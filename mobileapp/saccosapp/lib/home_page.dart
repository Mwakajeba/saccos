import 'package:flutter/material.dart';
import 'login_page.dart';
import 'loan_application_page.dart';
import 'profile_page.dart';
import 'loans_page.dart';
import 'loan_products_page.dart';
import 'models/user_session.dart';
import 'deposit_accounts_page.dart';
import 'loan_calculator_page.dart';
import 'contributions_page.dart';
import 'shares_page.dart';
import 'services/api_service.dart';
import 'account_transactions_page.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  int _selectedIndex = 0;
  bool _isBalanceVisible = true;
  List<dynamic> _contributions = [];
  List<dynamic> _shares = [];
  bool _isLoadingAssets = true;

  @override
  void initState() {
    super.initState();
    _loadAssets();
  }

  Future<void> _loadAssets() async {
    setState(() {
      _isLoadingAssets = true;
    });

    try {
      final userId = UserSession.instance.userId;
      if (userId != null) {
        final contributionsResponse = await ApiService.getContributions(userId);
        final sharesResponse = await ApiService.getShares(userId);

        setState(() {
          if (contributionsResponse['status'] == 200) {
            _contributions = contributionsResponse['contributions'] ?? [];
          }
          if (sharesResponse['status'] == 200) {
            _shares = sharesResponse['shares'] ?? [];
          }
          _isLoadingAssets = false;
        });
      } else {
        setState(() {
          _isLoadingAssets = false;
        });
      }
    } catch (e) {
      print('Error loading assets: $e');
      setState(() {
        _isLoadingAssets = false;
      });
    }
  }

  String _formatCurrency(double amount) {
    return amount.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]},',
    );
  }

  double _getTotalBalance() {
    final loans = UserSession.instance.loans;
    if (loans == null || loans.isEmpty) return 0.0;
    
    double total = 0.0;
    for (var loan in loans) {
      if (loan['status'] != 'rejected' && loan['status'] != 'cancelled') {
        total += (loan['total_due'] as num?)?.toDouble() ?? 0.0;
      }
    }
    return total;
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
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.1),
                  blurRadius: 30,
                  offset: const Offset(0, 0),
                ),
              ],
            ),
            child: Stack(
              children: [
                // Scrollable Content
                CustomScrollView(
                  slivers: [
                    // Top App Bar
                    SliverAppBar(
                      floating: true,
                      pinned: true,
                      elevation: 0,
                      backgroundColor: Colors.white.withOpacity(0.7),
                      flexibleSpace: ClipRRect(
                        child: Container(
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.7),
                            border: Border(
                              bottom: BorderSide(
                                color: Colors.grey.shade100,
                              ),
                            ),
                          ),
                          child: BackdropFilter(
                            filter: 
                                // ignore: deprecated_member_use
                                ColorFilter.mode(
                              Colors.transparent,
                              BlendMode.src,
                            ),
                            child: Padding(
                              padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text(
                                    'VisionApp',
                                    style: TextStyle(
                                      fontSize: 20,
                                      fontWeight: FontWeight.w800,
                                      color: Color(0xFF111813),
                                      letterSpacing: -0.3,
                                    ),
                                  ),
                                  Row(
                                    children: [
                                      Stack(
                                        children: [
                                          IconButton(
                                            onPressed: () {},
                                            icon: const Icon(
                                              Icons.notifications_outlined,
                                              size: 24,
                                              color: Color(0xFF111813),
                                            ),
                                          ),
                                          Positioned(
                                            right: 8,
                                            top: 8,
                                            child: Container(
                                              width: 10,
                                              height: 10,
                                              decoration: BoxDecoration(
                                                color: Colors.red,
                                                shape: BoxShape.circle,
                                                border: Border.all(
                                                  color: Colors.white,
                                                  width: 2,
                                                ),
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                      PopupMenuButton<String>(
                                        icon: const Icon(
                                          Icons.more_vert,
                                          color: Color(0xFF111813),
                                        ),
                                        onSelected: (value) {
                                          if (value == 'logout') {
                                            Navigator.of(context).pushReplacement(
                                              MaterialPageRoute(
                                                builder: (context) => const LoginPage(),
                                              ),
                                            );
                                          } else if (value == 'profile') {
                                            Navigator.of(context).push(
                                              MaterialPageRoute(
                                                builder: (context) => const ProfilePage(),
                                              ),
                                            );
                                          }
                                        },
                                        itemBuilder: (BuildContext context) => [
                                          const PopupMenuItem<String>(
                                            value: 'settings',
                                            child: Row(
                                              children: [
                                                Icon(Icons.settings_outlined, size: 20),
                                                SizedBox(width: 12),
                                                Text('Mpangilio'),
                                              ],
                                            ),
                                          ),
                                          const PopupMenuItem<String>(
                                            value: 'profile',
                                            child: Row(
                                              children: [
                                                Icon(Icons.person_outline, size: 20),
                                                SizedBox(width: 12),
                                                Text('Wasifu'),
                                              ],
                                            ),
                                          ),
                                          const PopupMenuItem<String>(
                                            value: 'logout',
                                            child: Row(
                                              children: [
                                                Icon(Icons.logout, size: 20, color: Colors.red),
                                                SizedBox(width: 12),
                                                Text('Toka nje', style: TextStyle(color: Colors.red)),
                                              ],
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                    // Content
                    SliverToBoxAdapter(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Profile Header
                          _buildProfileHeader(),
                          const SizedBox(height: 8),
                          // Total Balance Card
                          _buildBalanceCard(),
                          const SizedBox(height: 8),
                          // Quick Actions
                          _buildQuickActions(),
                          const SizedBox(height: 8),
                          // Asset Summary
                          _buildAssetSummary(),
                          const SizedBox(height: 8),
                          // Loan Status
                          _buildLoanStatus(),
                          const SizedBox(height: 8),
                          // Recent Transactions
                          _buildRecentTransactions(),
                          const SizedBox(height: 100),
                        ],
                      ),
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

  Widget _buildProfileHeader() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              border: Border.all(color: const Color(0xFF13EC5B), width: 2),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.1),
                  blurRadius: 8,
                ),
              ],
              image: const DecorationImage(
                image: NetworkImage(
                  'https://lh3.googleusercontent.com/aida-public/AB6AXuBd0N6cSEGk1hwdv89b438FQT5gXRemHtVi-UWe1tty6iO7Ql9FKPVRmNg6wKCGcIid3eo4MdY__f9HnroxVdYTUAFsr5GeEAkhGLXu-OdPkxoWzNJQWAIwOlazI-Bm3_WgGxJidWsHmvmft5kGdZF95JIsGWELX1uuItfWXVPusUPJADt77xUxhCyInd1rtAid8YNN4zHZekuRn2MisR3QqPXxae0S2GquZqINALMbiUQoKvtlF-13SRiJmzqA4veryzDRoXOVuIs',
                ),
                fit: BoxFit.cover,
              ),
            ),
          ),
          const SizedBox(width: 16),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Karibu tena,',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF6B7280),
                ),
              ),
              Text(
                UserSession.instance.name ?? 'Mwanachama',
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF111813),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildBalanceCard() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
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
            // Background Decoration
            Positioned(
              top: -24,
              right: -24,
              child: Container(
                width: 128,
                height: 128,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: const Color(0xFF13EC5B).withOpacity(0.2),
                  // ignore: deprecated_member_use
                  backgroundBlendMode: BlendMode.multiply,
                ),
              ),
            ),
            // Content
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Text(
                      'Jumla ya Salio',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w500,
                        color: Color(0xFF9CA3AF),
                      ),
                    ),
                    const SizedBox(width: 8),
                    GestureDetector(
                      onTap: () {
                        setState(() {
                          _isBalanceVisible = !_isBalanceVisible;
                        });
                      },
                      child: Icon(
                        _isBalanceVisible
                            ? Icons.visibility_outlined
                            : Icons.visibility_off_outlined,
                        size: 16,
                        color: const Color(0xFF9CA3AF),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.baseline,
                  textBaseline: TextBaseline.alphabetic,
                  children: [
                    const Text(
                      'TZS',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w500,
                        color: Color(0xFF9CA3AF),
                      ),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      _isBalanceVisible ? _formatCurrency(_getTotalBalance()) : '••••••',
                      style: const TextStyle(
                        fontSize: 36,
                        fontWeight: FontWeight.w800,
                        color: Colors.white,
                        letterSpacing: -1,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: const Color(0xFF13EC5B).withOpacity(0.2),
                    borderRadius: BorderRadius.circular(100),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.trending_up,
                        size: 14,
                        color: Color(0xFF13EC5B),
                      ),
                      SizedBox(width: 4),
                      Text(
                        '+12.5% mwezi huu',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF13EC5B),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickActions() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          _buildActionButton(
            icon: Icons.account_balance_wallet_outlined,
            label: 'Amana\nDhamana',
            isPrimary: true,
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (context) => const DepositAccountsPage(),
                ),
              );
            },
          ),
          _buildActionButton(
            icon: Icons.request_quote_outlined,
            label: 'Omba\nMkopo',
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (context) => const LoanApplicationPage(),
                ),
              );
            },
          ),
          _buildActionButton(
            icon: Icons.calculate_outlined,
            label: 'Kikokoto',
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (context) => const LoanCalculatorPage(),
                ),
              );
            },
          ),
          _buildActionButton(
            icon: Icons.shopping_bag_outlined,
            label: 'Bidhaa',
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (context) => const LoanProductsPage(),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildActionButton({
    required IconData icon,
    required String label,
    bool isPrimary = false,
    VoidCallback? onTap,
  }) {
    return GestureDetector(
      onTap: onTap ?? () {},
      child: Column(
        children: [
          Container(
            width: 56,
            height: 56,
            decoration: BoxDecoration(
              color: isPrimary ? const Color(0xFF13EC5B) : Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: isPrimary
                  ? null
                  : Border.all(color: Colors.grey.shade100),
              boxShadow: isPrimary
                  ? [
                      BoxShadow(
                        color: const Color(0xFF13EC5B).withOpacity(0.2),
                        blurRadius: 20,
                        offset: const Offset(0, 8),
                      ),
                    ]
                  : [],
            ),
            child: Icon(
              icon,
              size: 24,
              color: isPrimary ? const Color(0xFF102216) : const Color(0xFF111813),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF111813).withOpacity(0.8),
              height: 1.2,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAssetSummary() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 16),
          child: Text(
            'Michango na Hisa',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: Color(0xFF111813),
            ),
          ),
        ),
        const SizedBox(height: 12),
        SizedBox(
          height: 130,
          child: _isLoadingAssets
              ? const Center(
                  child: CircularProgressIndicator(),
                )
              : ListView(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  children: [
                    // Michango (Contributions)
                    if (_contributions.isNotEmpty)
                      ..._contributions.map((contribution) {
                        double balance = 0.0;
                        try {
                          if (contribution['balance'] is String) {
                            balance = double.parse(contribution['balance']);
                          } else {
                            balance = (contribution['balance'] as num?)?.toDouble() ?? 0.0;
                          }
                        } catch (e) {
                          balance = 0.0;
                        }
                        
                        String interestRate = '';
                        bool? isPositive;
                        try {
                          if (contribution['interest_rate'] != null) {
                            double rate = 0.0;
                            if (contribution['interest_rate'] is String) {
                              rate = double.parse(contribution['interest_rate']);
                            } else {
                              rate = (contribution['interest_rate'] as num).toDouble();
                            }
                            interestRate = '${rate.toStringAsFixed(1)}% p.a.';
                            isPositive = rate > 0 ? true : null;
                          } else {
                            interestRate = 'Imetulia';
                            isPositive = null;
                          }
                        } catch (e) {
                          interestRate = 'Imetulia';
                          isPositive = null;
                        }
                        
                        return Padding(
                          padding: const EdgeInsets.only(right: 16),
                          child: _buildAssetCard(
                            icon: Icons.account_balance_wallet,
                            label: contribution['product_name']?.toString() ?? 'Michango',
                            amount: _formatCurrency(balance),
                            change: interestRate,
                            isPositive: isPositive,
                            color: Colors.purple,
                            onTap: () {
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (context) => AccountTransactionsPage(
                                    accountId: contribution['id'],
                                    accountName: contribution['product_name']?.toString() ?? 'Michango',
                                    accountType: 'contribution',
                                  ),
                                ),
                              );
                            },
                          ),
                        );
                      }).toList(),
                    
                    // Michango ya Hisa (Shares)
                    if (_shares.isNotEmpty)
                      ..._shares.map((share) {
                        double totalValue = 0.0;
                        double shareBalance = 0.0;
                        try {
                          if (share['total_value'] is String) {
                            totalValue = double.parse(share['total_value']);
                          } else {
                            totalValue = (share['total_value'] as num?)?.toDouble() ?? 0.0;
                          }
                          
                          if (share['share_balance'] is String) {
                            shareBalance = double.parse(share['share_balance']);
                          } else {
                            shareBalance = (share['share_balance'] as num?)?.toDouble() ?? 0.0;
                          }
                        } catch (e) {
                          totalValue = 0.0;
                          shareBalance = 0.0;
                        }
                        
                        bool? isPositive;
                        try {
                          if (share['dividend_rate'] != null) {
                            double rate = 0.0;
                            if (share['dividend_rate'] is String) {
                              rate = double.parse(share['dividend_rate']);
                            } else {
                              rate = (share['dividend_rate'] as num).toDouble();
                            }
                            isPositive = rate > 0 ? true : null;
                          }
                        } catch (e) {
                          isPositive = null;
                        }
                        
                        return Padding(
                          padding: const EdgeInsets.only(right: 16),
                          child: _buildAssetCard(
                            icon: Icons.show_chart,
                            label: share['product_name']?.toString() ?? 'Hisa',
                            amount: _formatCurrency(totalValue),
                            change: '${shareBalance.toStringAsFixed(0)} hisa',
                            isPositive: isPositive,
                            color: Colors.blue,
                            onTap: () {
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (context) => AccountTransactionsPage(
                                    accountId: share['id'],
                                    accountName: share['product_name']?.toString() ?? 'Hisa',
                                    accountType: 'share',
                                  ),
                                ),
                              );
                            },
                          ),
                        );
                      }).toList(),
                    
                    // Placeholder if no data
                    if (_contributions.isEmpty && _shares.isEmpty)
                      _buildAssetCard(
                        icon: Icons.account_balance_wallet_outlined,
                        label: 'Hakuna Mali',
                        amount: '0',
                        change: 'Anza sasa',
                        isPositive: null,
                        color: Colors.grey,
                      ),
                  ],
                ),
        ),
      ],
    );
  }

  Widget _buildAssetCard({
    required IconData icon,
    required String label,
    required String amount,
    required String change,
    required bool? isPositive,
    required Color color,
    VoidCallback? onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 250,
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade100),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(icon, size: 20, color: color),
                ),
                const SizedBox(width: 8),
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF111813).withOpacity(0.7),
                  ),
                ),
              ],
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  amount,
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF111813),
                  ),
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    if (isPositive != null)
                      Icon(
                        isPositive ? Icons.arrow_upward : Icons.arrow_downward,
                        size: 12,
                        color: isPositive ? Colors.green : Colors.red,
                      ),
                    if (isPositive != null) const SizedBox(width: 2),
                    Text(
                      change,
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        color: isPositive == null
                            ? Colors.grey
                            : (isPositive ? Colors.green : Colors.red),
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

  Widget _buildLoanStatus() {
    // Calculate totals from active loans
    final loans = UserSession.instance.loans;
    double totalPaid = 0;
    double totalDue = 0;
    
    if (loans != null && loans.isNotEmpty) {
      for (var loan in loans) {
        if (loan['status'] == 'active') {
          totalPaid += ((loan['total_repaid'] as num?) ?? 0).toDouble();
          totalDue += ((loan['total_due'] as num?) ?? 0).toDouble();
        }
      }
    }
    
    double totalLoans = totalPaid + totalDue;
    double percentage = totalLoans > 0 ? (totalPaid / totalLoans * 100) : 0;
    
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade100),
        ),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Hali ya Mikopo',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF111813),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.green.shade50,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 6,
                        height: 6,
                        decoration: const BoxDecoration(
                          color: Colors.green,
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 4),
                      const Text(
                        'Active',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w700,
                          color: Colors.green,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                // Progress Circle
                SizedBox(
                  width: 80,
                  height: 80,
                  child: Stack(
                    children: [
                      SizedBox(
                        width: 80,
                        height: 80,
                        child: CircularProgressIndicator(
                          value: percentage / 100,
                          strokeWidth: 6,
                          backgroundColor: Colors.grey.shade200,
                          valueColor: const AlwaysStoppedAnimation<Color>(
                            Color(0xFF13EC5B),
                          ),
                        ),
                      ),
                      Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Text(
                              'MALIPO',
                              style: TextStyle(
                                fontSize: 9,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF6B7280),
                              ),
                            ),
                            Text(
                              '${percentage.toStringAsFixed(0)}%',
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w800,
                                color: Color(0xFF111813),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 20),
                Expanded(
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Jumla ya Mikopo',
                            style: TextStyle(
                              fontSize: 14,
                              color: Color(0xFF6B7280),
                            ),
                          ),
                          Text(
                            'TZS ${totalLoans.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]},')}',
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF111813),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      ClipRRect(
                        borderRadius: BorderRadius.circular(100),
                        child: LinearProgressIndicator(
                          value: percentage / 100,
                          minHeight: 4,
                          backgroundColor: Colors.grey.shade200,
                          valueColor: const AlwaysStoppedAnimation<Color>(
                            Color(0xFF13EC5B),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Jumla ya Malipo',
                            style: TextStyle(
                              fontSize: 12,
                              color: Color(0xFF6B7280),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 6,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: Colors.green.shade50,
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              'TZS ${totalPaid.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]},')}',
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF13EC5B),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRecentTransactions() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Miamala',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF111813),
                ),
              ),
              TextButton(
                onPressed: () {},
                child: const Text(
                  'Ona yote',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF13EC5B),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          _buildTransactionItem(
            icon: Icons.arrow_downward,
            title: 'Malipo ya Mkopo',
            time: 'Leo, 10:30 AM',
            amount: '- TZS 50,000',
            isPositive: false,
            iconColor: Colors.green,
          ),
          const SizedBox(height: 12),
          _buildTransactionItem(
            icon: Icons.arrow_upward,
            title: 'Weka Akiba',
            time: 'Jana, 04:15 PM',
            amount: '+ TZS 20,000',
            isPositive: true,
            iconColor: Colors.green,
          ),
        ],
      ),
    );
  }

  Widget _buildTransactionItem({
    required IconData icon,
    required String title,
    required String time,
    required String amount,
    required bool isPositive,
    required Color iconColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.grey.shade100),
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: isPositive
                  ? const Color(0xFF13EC5B).withOpacity(0.2)
                  : Colors.green.shade50,
              shape: BoxShape.circle,
            ),
            child: Icon(
              icon,
              size: 20,
              color: iconColor,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF111813),
                  ),
                ),
                Text(
                  time,
                  style: TextStyle(
                    fontSize: 10,
                    color: const Color(0xFF111813).withOpacity(0.6),
                  ),
                ),
              ],
            ),
          ),
          Text(
            amount,
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: isPositive ? const Color(0xFF13EC5B) : const Color(0xFF111813),
            ),
          ),
        ],
      ),
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
              _buildNavItem(Icons.home, 'Nyumbani', 0, filled: true),
              _buildNavItem(Icons.credit_score_outlined, 'Mikopo', 1),
              const SizedBox(width: 56), // Space for FAB
              _buildNavItem(Icons.payments_outlined, 'Michango', 2),
              _buildNavItem(Icons.person_outline, 'Wasifu', 3),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem(IconData icon, String label, int index,
      {bool filled = false}) {
    final isSelected = _selectedIndex == index;
    return GestureDetector(
      onTap: () {
        setState(() {
          _selectedIndex = index;
        });
        if (index == 1) {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (context) => const LoansPage(),
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
