import 'package:flutter/material.dart';

class DepositAccountTransactionsPage extends StatefulWidget {
  final Map<String, dynamic> account;
  const DepositAccountTransactionsPage({super.key, required this.account});

  @override
  State<DepositAccountTransactionsPage> createState() => _DepositAccountTransactionsPageState();
}

class _DepositAccountTransactionsPageState extends State<DepositAccountTransactionsPage> {
  bool showDeposits = true;

  // Demo transactions
  final List<Map<String, dynamic>> deposits = [
    {'amount': 50000, 'date': '2025-12-01', 'desc': 'Weka Akiba'},
    {'amount': 100000, 'date': '2025-11-15', 'desc': 'Weka Akiba'},
  ];
  final List<Map<String, dynamic>> withdrawals = [
    {'amount': 20000, 'date': '2025-12-10', 'desc': 'Toa Akiba'},
    {'amount': 50000, 'date': '2025-11-20', 'desc': 'Toa Akiba'},
  ];

  @override
  Widget build(BuildContext context) {
    final balance = widget.account['balance'] as num;
    final transactions = showDeposits ? deposits : withdrawals;
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.account['name'] as String),
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
      body: Column(
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
                  'TZS ${balance.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]},')}',
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
                              'Wekwa',
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
                              'Tolewa',
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
            child: ListView.builder(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: transactions.length,
              itemBuilder: (context, index) {
                final tx = transactions[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  child: ListTile(
                    leading: Icon(showDeposits ? Icons.arrow_downward : Icons.arrow_upward, color: showDeposits ? Colors.green : Colors.red),
                    title: Text('TZS ${(tx['amount'] as num).toStringAsFixed(0)}'),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(tx['date'] as String),
                        Text(tx['desc'] as String),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
