import 'package:flutter/material.dart';
import 'services/api_service.dart';

class AccountTransactionsPage extends StatefulWidget {
  final int accountId;
  final String accountName;
  final String accountType; // 'contribution' or 'share'

  const AccountTransactionsPage({
    super.key,
    required this.accountId,
    required this.accountName,
    required this.accountType,
  });

  @override
  State<AccountTransactionsPage> createState() => _AccountTransactionsPageState();
}

class _AccountTransactionsPageState extends State<AccountTransactionsPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;
  String? _errorMessage;
  List<dynamic> _deposits = [];
  List<dynamic> _withdrawals = [];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadTransactions();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadTransactions() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      Map<String, dynamic> response;
      
      if (widget.accountType == 'contribution') {
        response = await ApiService.getContributionTransactions(widget.accountId);
      } else {
        response = await ApiService.getShareTransactions(widget.accountId);
      }

      if (response['status'] == 200) {
        setState(() {
          _deposits = response['deposits'] ?? [];
          _withdrawals = response['withdrawals'] ?? [];
          _isLoading = false;
        });
      } else {
        setState(() {
          _errorMessage = response['message'] ?? 'Kuna tatizo, jaribu tena';
          _isLoading = false;
        });
      }
    } catch (e) {
      String errorMessage = 'Kuna tatizo, jaribu tena';
      
      if (e.toString().contains('TIMEOUT')) {
        errorMessage = 'Seva inachukua muda mrefu. Jaribu tena';
      } else if (e.toString().contains('NETWORK_ERROR')) {
        errorMessage = 'Hakuna mtandao. Angalia muunganisho wako';
      } else if (e.toString().contains('SERVER_ERROR')) {
        errorMessage = 'Tatizo la seva. Jaribu tena baadaye';
      }
      
      setState(() {
        _errorMessage = errorMessage;
        _isLoading = false;
      });
    }
  }

  String _formatCurrency(dynamic amount) {
    double value = 0.0;
    try {
      if (amount is String) {
        value = double.parse(amount);
      } else {
        value = (amount as num?)?.toDouble() ?? 0.0;
      }
    } catch (e) {
      value = 0.0;
    }
    return value.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]},',
    );
  }

  Widget _buildTransactionCard(dynamic transaction, bool isDeposit) {
    final amount = _formatCurrency(transaction['amount']);
    final date = transaction['date']?.toString().split('T')[0] ?? '';
    
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: (isDeposit ? Colors.green : Colors.red).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Icon(
                        isDeposit ? Icons.arrow_downward : Icons.arrow_upward,
                        color: isDeposit ? Colors.green : Colors.red,
                        size: 20,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          isDeposit ? 'Iliyoingia' : 'Iliyotoka',
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF111813),
                          ),
                        ),
                        Text(
                          date,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      'TSh $amount',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: isDeposit ? Colors.green : Colors.red,
                      ),
                    ),
                    if (widget.accountType == 'share' && transaction['shares'] != null)
                      Text(
                        '${_formatCurrency(transaction['shares'])} hisa',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey[600],
                        ),
                      ),
                  ],
                ),
              ],
            ),
            if (transaction['reference'] != null && transaction['reference'] != '')
              Padding(
                padding: const EdgeInsets.only(top: 12),
                child: Row(
                  children: [
                    Icon(Icons.receipt_outlined, size: 14, color: Colors.grey[500]),
                    const SizedBox(width: 4),
                    Text(
                      'Ref: ${transaction['reference']}',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                  ],
                ),
              ),
            if (transaction['notes'] != null && transaction['notes'] != '')
              Padding(
                padding: const EdgeInsets.only(top: 8),
                child: Text(
                  transaction['notes'],
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[600],
                    fontStyle: FontStyle.italic,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildTransactionsList(List<dynamic> transactions, bool isDeposit) {
    if (transactions.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              isDeposit ? Icons.arrow_downward : Icons.arrow_upward,
              size: 64,
              color: Colors.grey[400],
            ),
            const SizedBox(height: 16),
            Text(
              isDeposit ? 'Hakuna miamala iliyoingia' : 'Hakuna miamala iliyotoka',
              style: TextStyle(
                fontSize: 16,
                color: Colors.grey[600],
              ),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: transactions.length,
      itemBuilder: (context, index) {
        return _buildTransactionCard(transactions[index], isDeposit);
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F8F6),
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Miamala',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: Color(0xFF111813),
              ),
            ),
            Text(
              widget.accountName,
              style: TextStyle(
                fontSize: 12,
                color: Colors.grey[600],
                fontWeight: FontWeight.normal,
              ),
            ),
          ],
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Color(0xFF111813)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadTransactions,
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          labelColor: const Color(0xFF111813),
          unselectedLabelColor: Colors.grey,
          indicatorColor: const Color(0xFF111813),
          tabs: [
            Tab(
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.arrow_downward, size: 16),
                  const SizedBox(width: 8),
                  Text('Iliyoingia (${_deposits.length})'),
                ],
              ),
            ),
            Tab(
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.arrow_upward, size: 16),
                  const SizedBox(width: 8),
                  Text('Iliyotoka (${_withdrawals.length})'),
                ],
              ),
            ),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _errorMessage != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.error_outline,
                        size: 64,
                        color: Colors.grey[400],
                      ),
                      const SizedBox(height: 16),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 32),
                        child: Text(
                          _errorMessage!,
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            fontSize: 16,
                            color: Colors.grey[600],
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                      ElevatedButton.icon(
                        onPressed: _loadTransactions,
                        icon: const Icon(Icons.refresh),
                        label: const Text('Jaribu Tena'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF111813),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 24,
                            vertical: 12,
                          ),
                        ),
                      ),
                    ],
                  ),
                )
              : TabBarView(
                  controller: _tabController,
                  children: [
                    RefreshIndicator(
                      onRefresh: _loadTransactions,
                      child: _buildTransactionsList(_deposits, true),
                    ),
                    RefreshIndicator(
                      onRefresh: _loadTransactions,
                      child: _buildTransactionsList(_withdrawals, false),
                    ),
                  ],
                ),
    );
  }
}
