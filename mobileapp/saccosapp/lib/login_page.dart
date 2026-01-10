import 'package:flutter/material.dart';
import 'home_page.dart';
import 'services/api_service.dart';
import 'models/user_session.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  bool _isPasswordVisible = false;
  bool _isSwahili = true;
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void dispose() {
    _phoneController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F8F6),
      body: Center(
        child: Container(
          constraints: const BoxConstraints(maxWidth: 400, maxHeight: 900),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFDBE6DF).withOpacity(0.5)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.1),
                blurRadius: 20,
                offset: const Offset(0, 10),
              ),
            ],
          ),
          child: Column(
            children: [
              // Top Bar: Language Switcher
              Padding(
                padding: const EdgeInsets.fromLTRB(24, 24, 24, 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFFF3F4F6),
                        borderRadius: BorderRadius.circular(100),
                        border: Border.all(color: const Color(0xFFDBE6DF)),
                      ),
                      padding: const EdgeInsets.all(4),
                      child: Row(
                        children: [
                          _buildLanguageButton('Kiswahili', true),
                          _buildLanguageButton('English', false),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              // Scrollable Content
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Column(
                    children: [
                      const SizedBox(height: 24),
                      // Logo / Hero
                      _buildHeroSection(),
                      const SizedBox(height: 8),
                      // Login Form
                      _buildLoginForm(),
                      const SizedBox(height: 32),
                      // Footer
                      _buildFooter(),
                      const SizedBox(height: 24),
                    ],
                  ),
                ),
              ),
              // Bottom Gradient Decoration
              Container(
                height: 4,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      Colors.transparent,
                      const Color(0xFF13EC5B).withOpacity(0.3),
                      Colors.transparent,
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLanguageButton(String text, bool isActive) {
    return GestureDetector(
      onTap: () {
        setState(() {
          _isSwahili = text == 'Kiswahili';
        });
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: (text == 'Kiswahili' && _isSwahili) || (text == 'English' && !_isSwahili)
              ? Colors.white
              : Colors.transparent,
          borderRadius: BorderRadius.circular(100),
          boxShadow: (text == 'Kiswahili' && _isSwahili) || (text == 'English' && !_isSwahili)
              ? [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.05),
                    blurRadius: 4,
                    offset: const Offset(0, 2),
                  ),
                ]
              : [],
        ),
        child: Text(
          text,
          style: TextStyle(
            fontSize: 12,
            fontWeight: (text == 'Kiswahili' && _isSwahili) || (text == 'English' && !_isSwahili)
                ? FontWeight.w700
                : FontWeight.w500,
            color: (text == 'Kiswahili' && _isSwahili) || (text == 'English' && !_isSwahili)
                ? const Color(0xFF111813)
                : const Color(0xFF61896F),
            height: 1.0,
          ),
        ),
      ),
    );
  }

  Widget _buildHeroSection() {
    return Column(
      children: [
        Container(
          width: 80,
          height: 80,
          decoration: BoxDecoration(
            color: const Color(0xFF13EC5B).withOpacity(0.1),
            borderRadius: BorderRadius.circular(16),
          ),
          child: const Icon(
            Icons.savings_outlined,
            size: 40,
            color: Color(0xFF13EC5B),
          ),
        ),
        const SizedBox(height: 16),
        const Text(
          'Karibu tena',
          style: TextStyle(
            fontSize: 28,
            fontWeight: FontWeight.w800,
            color: Color(0xFF111813),
            height: 1.2,
            letterSpacing: -0.5,
          ),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 8),
        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 16),
          child: Text(
            'Ingia kwenye akaunti yako ya kikundi kusimamia akiba na mikopo.',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: Color(0xFF61896F),
              height: 1.4,
            ),
            textAlign: TextAlign.center,
          ),
        ),
      ],
    );
  }

  Widget _buildLoginForm() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Phone Number Input
        const Text(
          'Namba ya Simu',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w700,
            color: Color(0xFF111813),
          ),
        ),
        const SizedBox(height: 8),
        Container(
          height: 56,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFDBE6DF)),
          ),
          child: Row(
            children: [
              // Flag and Prefix
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                decoration: const BoxDecoration(
                  color: Color(0xFFF9FAFB),
                  border: Border(
                    right: BorderSide(color: Color(0xFFDBE6DF)),
                  ),
                ),
                child: const Row(
                  children: [
                    Text('🇹🇿', style: TextStyle(fontSize: 18)),
                    SizedBox(width: 8),
                    Text(
                      '+255',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF111813),
                      ),
                    ),
                  ],
                ),
              ),
              // Input
              Expanded(
                child: TextField(
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(
                    hintText: '7XX XXX XXX',
                    hintStyle: TextStyle(
                      color: Color(0x8061896F),
                      fontWeight: FontWeight.w500,
                    ),
                    border: InputBorder.none,
                    contentPadding: EdgeInsets.symmetric(horizontal: 16),
                  ),
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w500,
                    color: Color(0xFF111813),
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),
        // Password Input
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Nenosiri',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w700,
                color: Color(0xFF111813),
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        Container(
          height: 56,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFDBE6DF)),
          ),
          child: Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _passwordController,
                  obscureText: !_isPasswordVisible,
                  decoration: const InputDecoration(
                    hintText: 'Weka nenosiri lako',
                    hintStyle: TextStyle(
                      color: Color(0x8061896F),
                      fontWeight: FontWeight.w500,
                    ),
                    border: InputBorder.none,
                    contentPadding: EdgeInsets.symmetric(horizontal: 16),
                  ),
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w500,
                    color: Color(0xFF111813),
                  ),
                ),
              ),
              IconButton(
                onPressed: () {
                  setState(() {
                    _isPasswordVisible = !_isPasswordVisible;
                  });
                },
                icon: Icon(
                  _isPasswordVisible ? Icons.visibility : Icons.visibility_off,
                  color: const Color(0xFF61896F),
                  size: 24,
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 8),
        // Forgot Password Link
        Align(
          alignment: Alignment.centerRight,
          child: TextButton(
            onPressed: () {},
            style: TextButton.styleFrom(
              padding: EdgeInsets.zero,
              minimumSize: Size.zero,
              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
            ),
            child: const Text(
              'Umesahau Nenosiri?',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w700,
                color: Color(0xFF13EC5B),
              ),
            ),
          ),
        ),
        const SizedBox(height: 16),
        // Login Button
        SizedBox(
          width: double.infinity,
          height: 56,
          child: ElevatedButton(
            onPressed: _isLoading ? null : _handleLogin,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF13EC5B),
              foregroundColor: const Color(0xFF102216),
              elevation: 8,
              shadowColor: const Color(0xFF13EC5B).withOpacity(0.2),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: _isLoading
                ? const SizedBox(
                    height: 20,
                    width: 20,
                    child: CircularProgressIndicator(
                      color: Color(0xFF102216),
                      strokeWidth: 2,
                    ),
                  )
                : const Text(
                    'Ingia',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 0.5,
                    ),
                  ),
          ),
        ),
        if (_errorMessage != null) ...[
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(12),
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
                      fontSize: 14,
                      color: Colors.red.shade700,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ],
    );
  }

  Future<void> _handleLogin() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final phone = _phoneController.text.trim();
    final password = _passwordController.text.trim();

    if (phone.isEmpty || password.isEmpty) {
      setState(() {
        _isLoading = false;
        _errorMessage = 'Tafadhali jaza simu na nywila';
      });
      return;
    }

    try {
      final response = await ApiService.login(phone, password);
      
      print('=== LOGIN PAGE - Full Response ===');
      print(response);
      print('==================================');
      
      if (response['status'] == 200) {
        // Save user session - extract from data field
        final userData = response['data'] ?? response;
        print('=== USER DATA TO SAVE ===');
        print(userData);
        print('=========================');
        UserSession.instance.setUserData(userData);
        
        if (mounted) {
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(builder: (context) => const HomePage()),
          );
        }
      } else {
        setState(() {
          _errorMessage = response['message'] ?? 'Kuna tatizo, jaribu tena';
        });
      }
    } catch (e) {
      String errorMsg = 'Kuna tatizo, jaribu tena';
      final errorStr = e.toString();
      
      if (errorStr.contains('TIMEOUT')) {
        errorMsg = 'Seva imechukua muda mrefu sana. Jaribu tena';
      } else if (errorStr.contains('NETWORK_ERROR')) {
        errorMsg = 'Hakuna mtandao. Angalia muunganisho wako';
      } else if (errorStr.contains('AUTH_ERROR')) {
        errorMsg = errorStr.split(':').length > 1 
            ? errorStr.split(':')[1] 
            : 'Simu au nywila si sahihi';
      } else if (errorStr.contains('SERVER_ERROR')) {
        errorMsg = 'Seva haipo kwa sasa. Jaribu baadaye';
      }
      
      setState(() {
        _errorMessage = errorMsg;
      });
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  Widget _buildFooter() {
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text(
              'Huna akaunti?',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: Color(0xFF61896F),
              ),
            ),
            const SizedBox(width: 6),
            TextButton(
              onPressed: () {},
              style: TextButton.styleFrom(
                padding: EdgeInsets.zero,
                minimumSize: Size.zero,
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
              child: const Text(
                'Jisajili',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF13EC5B),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        // Security Indicator
        Opacity(
          opacity: 0.6,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.lock,
                size: 16,
                color: Color(0xFF61896F),
              ),
              const SizedBox(width: 8),
              const Text(
                'Imelindwa na Usimbuaji wa 256-bit',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF61896F),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
