import 'package:flutter/material.dart';
import 'deposit_account_transactions.dart';

class DepositAccountsPage extends StatefulWidget {
  const DepositAccountsPage({super.key});

  @override
  State<DepositAccountsPage> createState() => _DepositAccountsPageState();
}

class _DepositAccountsPageState extends State<DepositAccountsPage> {
  bool _isBalanceVisible = false;

  @override
  Widget build(BuildContext context) {
    // Demo data for deposit accounts
    final accounts = [
      {
        'name': 'Amana ya Akiba',
        'opened': '2022-01-15',
        'balance': 1200000,
        'deposited': 1500000,
        'withdrawn': 300000,
      },
      {
        'name': 'Amana ya Elimu',
        'opened': '2023-03-10',
        'balance': 500000,
        'deposited': 600000,
        'withdrawn': 100000,
      },
      {
        'name': 'Amana ya Ujenzi',
        'opened': '2024-07-01',
        'balance': 800000,
        'deposited': 900000,
        'withdrawn': 100000,
      },
    ];

    double totalBalance = accounts.fold(0, (sum, acc) => sum + (acc['balance'] as num));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Amana Dhamana'),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.black),
        titleTextStyle: const TextStyle(
          color: Colors.black,
          fontSize: 20,
          fontWeight: FontWeight.w700,
        ),
      ),
      backgroundColor: const Color(0xFFF6F8F6),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 24),
              decoration: BoxDecoration(
                color: const Color(0xFF111813),
                borderRadius: BorderRadius.circular(15),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Jumla ya Salio',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                          letterSpacing: -0.5,
                        ),
                      ),
                      IconButton(
                        icon: Icon(
                          _isBalanceVisible ? Icons.visibility : Icons.visibility_off,
                          color: Colors.white,
                        ),
                        onPressed: () {
                          setState(() {
                            _isBalanceVisible = !_isBalanceVisible;
                          });
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    _isBalanceVisible
                        ? 'TZS ${totalBalance.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]},')}'
                        : 'TZS ••••••',
                    style: const TextStyle(
                      fontSize: 40,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                      letterSpacing: -1,
                    ),
                  ),
                ],
              ),
            ),
                    const SizedBox(height: 24),
                    const Text(
                      'Akaunti za Amana',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF111813),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Expanded(
                      child: ListView.builder(
                        itemCount: accounts.length,
                        itemBuilder: (context, index) {
                          final acc = accounts[index];
                          return GestureDetector(
                            onTap: () {
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (context) => DepositAccountTransactionsPage(account: acc),
                                ),
                              );
                            },
                            child: Card(
                              margin: const EdgeInsets.only(bottom: 16),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Padding(
                                padding: const EdgeInsets.all(16),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      acc['name'] as String,
                                      style: const TextStyle(
                                        fontSize: 18,
                                        fontWeight: FontWeight.w700,
                                        color: Color(0xFF111813),
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          'Ilifunguliwa: ${acc['opened']}',
                                          style: const TextStyle(
                                            fontSize: 12,
                                            color: Color(0xFF6B7280),
                                          ),
                                        ),
                                        Text(
                                          'Salio: TZS ${(acc['balance'] as num).toStringAsFixed(0)}',
                                          style: const TextStyle(
                                            fontSize: 14,
                                            fontWeight: FontWeight.w700,
                                            color: Color(0xFF13EC5B),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          'Kiasi kilichowekwa: TZS ${(acc['deposited'] as num).toStringAsFixed(0)}',
                                          style: const TextStyle(
                                            fontSize: 12,
                                            color: Color(0xFF111813),
                                          ),
                                        ),
                                        Text(
                                          'Kiasi kilichotolewa: TZS ${(acc['withdrawn'] as num).toStringAsFixed(0)}',
                                          style: const TextStyle(
                                            fontSize: 12,
                                            color: Colors.red,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),
            );
          }
        }
