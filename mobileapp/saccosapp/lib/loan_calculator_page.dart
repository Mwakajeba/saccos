import 'package:flutter/material.dart';
import 'services/api_service.dart';

class LoanCalculatorPage extends StatefulWidget {
  const LoanCalculatorPage({super.key});

  @override
  State<LoanCalculatorPage> createState() => _LoanCalculatorPageState();
}

class _LoanCalculatorPageState extends State<LoanCalculatorPage> {
  List<dynamic> loanProducts = [];
  dynamic selectedProduct;
  String? interestCycle;
  final TextEditingController amountController = TextEditingController();
  final TextEditingController durationController = TextEditingController();
  final TextEditingController interestController = TextEditingController(text: '10');
  Map<String, dynamic>? result;
  bool loadingProducts = false;
  bool calculating = false;

  @override
  void initState() {
    super.initState();
    fetchLoanProducts();
  }

  Future<void> fetchLoanProducts() async {
    setState(() { loadingProducts = true; });
    try {
      final response = await ApiService.getLoanProducts();
      if (response['status'] == 200 && response['products'] != null) {
        setState(() {
          loanProducts = response['products'];
        });
      }
    } catch (e) {
      // Handle error
    } finally {
      setState(() { loadingProducts = false; });
    }
  }

  Future<void> calculateLoan() async {
    if (selectedProduct == null || amountController.text.isEmpty || durationController.text.isEmpty) return;
    setState(() { calculating = true; });
    // Simulate calculation or call API
    await Future.delayed(const Duration(seconds: 1));
    setState(() {
      result = {
        'product': selectedProduct!['name'],
        'amount': amountController.text,
        'duration': durationController.text,
        'interest': '${interestController.text}%',
        'monthlyPayment': 'TZS 50,000',
        'totalPayment': 'TZS 600,000',
      };
      calculating = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Kikokotoa Mkopo'),
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
        child: SingleChildScrollView(
          child: Column(
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
                child: loadingProducts
                    ? const Padding(
                        padding: EdgeInsets.all(16),
                        child: Center(child: CircularProgressIndicator()),
                      )
                    : DropdownButtonFormField<dynamic>(
                        value: selectedProduct,
                        decoration: const InputDecoration(
                          hintText: 'Chagua bidhaa',
                          border: OutlineInputBorder(),
                          contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        ),
                        items: loanProducts.map<DropdownMenuItem<dynamic>>((product) {
                          return DropdownMenuItem(
                            value: product,
                            child: Text(product['name']),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() {
                            selectedProduct = value;
                            interestController.text = (value['minimum_interest'] ?? '10').toString();
                            durationController.text = (value['minimum_period'] ?? '1').toString();
                            amountController.text = (value['minimum_principal'] ?? '1000').toString();
                            interestCycle = value['interest_cycle']?.toString();
                          });
                        },
                      ),
              ),
              if (selectedProduct != null) ...[
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Riba: ${selectedProduct['minimum_interest']}% - ${selectedProduct['maximum_interest']}%'),
                      Text('Muda: ${selectedProduct['minimum_period']} - ${selectedProduct['maximum_period']} ${selectedProduct['interest_cycle']}'),
                      Text('Kiasi: ${selectedProduct['minimum_principal']} - ${selectedProduct['maximum_principal']} TZS'),
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 24),
              Row(
                children: [
                  Padding(
                    padding: const EdgeInsets.only(left: 16),
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
                      onChanged: (val) {
                        if (val.isEmpty || val == 'null') {
                          amountController.text = selectedProduct != null && selectedProduct['minimum_principal'] != null ? selectedProduct['minimum_principal'].toString() : '1000';
                        }
                      },
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              const Text(
                'Riba (%)',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF111813),
                ),
              ),
              const SizedBox(height: 8),
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
                child: TextField(
                  controller: interestController,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(
                    border: InputBorder.none,
                    contentPadding: EdgeInsets.symmetric(horizontal: 16),
                    hintText: 'Riba',
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
                  onChanged: (val) {
                    if (val.isEmpty || val == 'null') {
                      interestController.text = selectedProduct != null && selectedProduct['minimum_interest'] != null ? selectedProduct['minimum_interest'].toString() : '10';
                    }
                  },
                ),
              ),
              const SizedBox(height: 24),
              Text(
                'Muda wa Kurejesha (${selectedProduct != null ? selectedProduct['interest_cycle'] : '...'})',
                style: const TextStyle(
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
                        int duration = int.tryParse(durationController.text) ?? 1;
                        if (duration > 1) {
                          duration--;
                          durationController.text = duration.toString();
                        }
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
                          '${(durationController.text.isEmpty || durationController.text == 'null') ? (selectedProduct != null && selectedProduct['minimum_period'] != null ? selectedProduct['minimum_period'] : '1') : durationController.text} ${selectedProduct != null ? selectedProduct['interest_cycle'] ?? '' : ''}',
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
                        int duration = int.tryParse(durationController.text) ?? 1;
                        if (duration < 36) {
                          duration++;
                          durationController.text = duration.toString();
                        }
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
              const SizedBox(height: 24),
              if (result != null)
                Card(
                  color: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Bidhaa: ${result!['product']}', style: const TextStyle(fontWeight: FontWeight.w700)),
                        Text('Kiasi: ${result!['amount']}'),
                        Text('Muda: ${result!['duration']} ${selectedProduct != null ? selectedProduct['interest_cycle'] : ''}'),
                        Text('Riba: ${result!['interest']}'),
                        Text('Malipo ya ${selectedProduct != null ? selectedProduct['interest_cycle'] : ''}: ${result!['monthlyPayment']}'),
                        Text('Jumla ya malipo: ${result!['totalPayment']}'),
                      ],
                    ),
                  ),
                ),
              const SizedBox(height: 100),
            ],
          ),
        ),
      ),
      bottomNavigationBar: Container(
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
              onPressed: calculating ? null : calculateLoan,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF13EC5B),
                foregroundColor: const Color(0xFF052E16),
                elevation: 8,
                shadowColor: const Color(0xFF13EC5B).withOpacity(0.25),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: calculating
                  ? const CircularProgressIndicator(color: Colors.white)
                  : const Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          'Kokotoa',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        SizedBox(width: 8),
                        Icon(Icons.calculate_outlined, size: 20),
                      ],
                    ),
            ),
          ),
        ),
      ),
    );
  }
}
