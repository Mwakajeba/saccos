import 'package:flutter/material.dart';
import 'services/api_service.dart';

class DepositAccountTransactionsPage extends StatefulWidget {
  final Map<String, dynamic> account;
  final String accountType; // 'contribution' or 'share'
  
  const DepositAccountTransactionsPage({
    super.key,
    required this.account,
    this.accountType = 'contribution',
  });

  @override
  State<DepositAccountTransactionsPage> createState() => _DepositAccountTransactionsPageState();
}

class _DepositAccountTransactionsPageState extends State<DepositAccountTransactionsPage> {
  bool showDeposits = true;
  bool _isLoading = true;
  String? _errorMessage;
  List<dynamic> _deposits = [];
  List<dynamic> _withdrawals = [];

  @override
  void initState() {
    super.initState();
    _loadTransactions();
  }

  Future<void> _loadTransactions() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      Map<String, dynamic> response;
      final accountId = widget.account['id'] as int?;

      if (accountId == null) {
        setState(() {
          _errorMessage = 'Account ID not found';
          _isLoading = false;
        });
        return;
      }

      if (widget.accountType == 'contribution') {
        response = await ApiService.getContributionTransactions(accountId);
      } else {
        response = await ApiService.getShareTransactions(accountId);
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
        value = double.parse(amount.replaceAll(',', ''));
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
    final date = transaction['date']?.toString().split('T')[0] ?? transaction['date']?.toString() ?? '';
    
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

    return RefreshIndicator(
      onRefresh: _loadTransactions,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: transactions.length,
        itemBuilder: (context, index) {
          return _buildTransactionCard(transactions[index], isDeposit);
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final balance = widget.account['balance'] as num? ?? 0.0;
    final transactions = showDeposits ? _deposits : _withdrawals;

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.account['name'] as String? ?? 'Account'),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.black),
        titleTextStyle: const TextStyle(
          color: Colors.black,
          fontSize: 20,
          fontWeight: FontWeight.w700,
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadTransactions,
          ),
        ],
      ),
      backgroundColor: const Color(0xFFF6F8F6),
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
              : Column(
                  children: [
                    Container(
                      width: double.infinity,
                      margin: const EdgeInsets.all(16),
                      padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 20),
                      decoration: BoxDecoration(
                        color: const Color(0xFF111813),
                        borderRadius: BorderRadius.circular(15),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Salio',
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w700,
                              color: Colors.white,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'TZS ${_formatCurrency(balance)}',
                            style: const TextStyle(
                              fontSize: 28,
                              fontWeight: FontWeight.w800,
                              color: Colors.white,
                              letterSpacing: -1,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Miamala',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF111813),
                            ),
                          ),
                          const SizedBox(height: 10),
                          Container(
                            height: 40,
                            decoration: BoxDecoration(
                              color: Colors.grey.shade200,
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Row(
                              children: [
                                Expanded(
                                  child: GestureDetector(
                                    onTap: () {
                                      setState(() {
                                        showDeposits = true;
                                      });
                                    },
                                    child: Container(
                                      alignment: Alignment.center,
                                      decoration: BoxDecoration(
                                        color: showDeposits ? const Color(0xFF13EC5B) : Colors.transparent,
                                        borderRadius: BorderRadius.circular(20),
                                      ),
                                      child: Text(
                                        'Wekwa (${_deposits.length})',
                                        style: TextStyle(
                                          color: showDeposits ? Colors.white : Colors.black,
                                          fontWeight: FontWeight.w700,
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                                Expanded(
                                  child: GestureDetector(
                                    onTap: () {
                                      setState(() {
                                        showDeposits = false;
                                      });
                                    },
                                    child: Container(
                                      alignment: Alignment.center,
                                      decoration: BoxDecoration(
                                        color: !showDeposits ? const Color(0xFF13EC5B) : Colors.transparent,
                                        borderRadius: BorderRadius.circular(20),
                                      ),
                                      child: Text(
                                        'Tolewa (${_withdrawals.length})',
                                        style: TextStyle(
                                          color: !showDeposits ? Colors.white : Colors.black,
                                          fontWeight: FontWeight.w700,
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 8),
                    Expanded(
                      child: _buildTransactionsList(transactions, showDeposits),
                    ),
                  ],
                ),
    );
  }
}
