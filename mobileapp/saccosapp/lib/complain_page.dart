import 'package:flutter/material.dart';
import 'services/api_service.dart';
import 'models/user_session.dart';

class ComplainPage extends StatefulWidget {
  const ComplainPage({super.key});

  @override
  State<ComplainPage> createState() => _ComplainPageState();
}

class _ComplainPageState extends State<ComplainPage> {
  int _selectedTab = 0;
  final TextEditingController _descriptionController = TextEditingController();
  String? _selectedCategoryId;
  List<dynamic> _categories = [];
  List<dynamic> _complains = [];
  bool _isLoadingCategories = false;
  bool _isLoadingComplains = false;
  bool _isSubmitting = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadCategories();
    _loadComplains();
  }

  @override
  void dispose() {
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _loadCategories() async {
    setState(() {
      _isLoadingCategories = true;
    });

    try {
      final response = await ApiService.getComplainCategories();
      if (response['status'] == 200 && response['categories'] != null) {
        setState(() {
          _categories = response['categories'];
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Imeshindwa kupata aina za malalamiko';
      });
    } finally {
      setState(() {
        _isLoadingCategories = false;
      });
    }
  }

  Future<void> _loadComplains() async {
    final userSession = UserSession.instance;
    if (userSession.userId == null) return;

    setState(() {
      _isLoadingComplains = true;
    });

    try {
      final response = await ApiService.getCustomerComplains(userSession.userId!);
      if (response['status'] == 200 && response['complains'] != null) {
        setState(() {
          _complains = response['complains'];
        });
      }
    } catch (e) {
      print('Error loading complains: $e');
    } finally {
      setState(() {
        _isLoadingComplains = false;
      });
    }
  }

  Future<void> _submitComplain() async {
    if (_selectedCategoryId == null) {
      _showError('Tafadhali chagua aina ya malalamiko');
      return;
    }

    if (_descriptionController.text.trim().isEmpty) {
      _showError('Tafadhali andika maelezo ya malalamiko');
      return;
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
      final complainData = {
        'customer_id': userSession.userId,
        'complain_category_id': int.parse(_selectedCategoryId!),
        'description': _descriptionController.text.trim(),
      };

      final response = await ApiService.submitComplain(complainData);

      if (response['status'] == 200) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Malalamiko yako yamewasilishwa kwa mafanikio'),
              backgroundColor: Colors.green,
            ),
          );
          // Clear form
          _selectedCategoryId = null;
          _descriptionController.clear();
          setState(() {});
          // Reload complains and switch to view tab
          _loadComplains();
          setState(() {
            _selectedTab = 1;
          });
        }
      }
    } catch (e) {
      String errorMsg = 'Imeshindwa kuwasilisha malalamiko';
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
          _buildTab('Toa Malalamiko', 0),
          _buildTab('Malalamiko', 1),
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
          if (index == 1) {
            _loadComplains();
          }
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

  String _getStatusText(String status) {
    switch (status) {
      case 'pending':
        return 'Inasubiri';
      case 'in_progress':
        return 'Inaendelea';
      case 'resolved':
        return 'Imetatuliwa';
      case 'closed':
        return 'Imefungwa';
      default:
        return status;
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'pending':
        return Colors.orange;
      case 'in_progress':
        return Colors.blue;
      case 'resolved':
        return Colors.green;
      case 'closed':
        return Colors.grey;
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F8F6),
      body: SafeArea(
        child: Column(
          children: [
            // App Bar
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                border: Border(
                  bottom: BorderSide(color: Colors.grey.shade200),
                ),
              ),
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
                      'Malalamiko',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF111813),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            // Tabs
            _buildTabs(),
            // Tab Content
            Expanded(
              child: _selectedTab == 0
                  ? _buildSubmitTab()
                  : _buildComplainsListTab(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSubmitTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.only(left: 16, right: 16, top: 16, bottom: 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
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
          // Category Selection
          const Text(
            'Aina ya Malalamiko',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: Color(0xFF111813),
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Chagua aina ya malalamiko unayotaka kuwasilisha.',
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
            child: _isLoadingCategories
                ? const Padding(
                    padding: EdgeInsets.all(16),
                    child: Center(child: CircularProgressIndicator()),
                  )
                : DropdownButtonFormField<String>(
                    value: _selectedCategoryId,
                    decoration: const InputDecoration(
                      border: InputBorder.none,
                      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                      hintText: 'Chagua aina ya malalamiko...',
                      hintStyle: TextStyle(color: Color(0xFF9CA3AF)),
                    ),
                    icon: const Icon(Icons.expand_more, color: Color(0xFF9CA3AF)),
                    style: const TextStyle(
                      fontSize: 16,
                      color: Color(0xFF111813),
                    ),
                    dropdownColor: Colors.white,
                    isExpanded: true,
                    items: _categories.map<DropdownMenuItem<String>>((category) {
                      return DropdownMenuItem<String>(
                        value: category['id'].toString(),
                        child: Text(
                          category['name'] ?? '',
                          style: const TextStyle(
                            fontWeight: FontWeight.w600,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      );
                    }).toList(),
                    onChanged: (value) {
                      setState(() {
                        _selectedCategoryId = value;
                      });
                    },
                  ),
          ),
          // Show category description if selected
          if (_selectedCategoryId != null)
            Builder(
              builder: (context) {
                final selectedCategory = _categories.firstWhere(
                  (c) => c['id'].toString() == _selectedCategoryId,
                  orElse: () => null,
                );
                if (selectedCategory != null && 
                    selectedCategory['description'] != null && 
                    selectedCategory['description'].toString().isNotEmpty) {
                  return Container(
                    margin: const EdgeInsets.only(top: 12),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.blue.shade50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.blue.shade200),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(Icons.info_outline, color: Colors.blue.shade700, size: 20),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            selectedCategory['description'],
                            style: TextStyle(
                              color: Colors.blue.shade800,
                              fontSize: 12,
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                }
                return const SizedBox.shrink();
              },
            ),
          const SizedBox(height: 24),
          // Description
          const Text(
            'Maelezo',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: Color(0xFF111813),
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Andika maelezo kamili ya malalamiko yako.',
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
            child: TextField(
              controller: _descriptionController,
              maxLines: 6,
              decoration: const InputDecoration(
                border: InputBorder.none,
                contentPadding: EdgeInsets.all(16),
                hintText: 'Andika maelezo ya malalamiko yako hapa...',
                hintStyle: TextStyle(color: Color(0xFF9CA3AF)),
              ),
              style: const TextStyle(
                fontSize: 16,
                color: Color(0xFF111813),
              ),
            ),
          ),
          const SizedBox(height: 32),
          // Submit Button
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton(
              onPressed: _isSubmitting ? null : _submitComplain,
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
                          'Wasilisha Malalamiko',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        SizedBox(width: 8),
                        Icon(Icons.send, size: 20),
                      ],
                    ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildComplainsListTab() {
    if (_isLoadingComplains) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_complains.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.feedback_outlined,
              size: 64,
              color: Colors.grey.shade400,
            ),
            const SizedBox(height: 16),
            Text(
              'Hakuna malalamiko',
              style: TextStyle(
                fontSize: 16,
                color: Colors.grey.shade600,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Bofya "Toa Malalamiko" kuwasilisha malalamiko yako',
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey.shade500,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadComplains,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _complains.length,
        itemBuilder: (context, index) {
          final complain = _complains[index];
          return Container(
            margin: const EdgeInsets.only(bottom: 12),
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
            child: ExpansionTile(
              tilePadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
              title: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    complain['category_name'] ?? 'N/A',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF111813),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: _getStatusColor(complain['status'] ?? 'pending').withOpacity(0.1),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          _getStatusText(complain['status'] ?? 'pending'),
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: _getStatusColor(complain['status'] ?? 'pending'),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        complain['created_at'] ?? '',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade600,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              children: [
                // Description
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade50,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Maelezo:',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF6B7280),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        complain['description'] ?? '',
                        style: const TextStyle(
                          fontSize: 14,
                          color: Color(0xFF111813),
                        ),
                      ),
                    ],
                  ),
                ),
                // Response Section
                if (complain['response'] != null && complain['response'].toString().isNotEmpty) ...[
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.green.shade50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.green.shade200),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(Icons.check_circle, color: Colors.green.shade700, size: 16),
                            const SizedBox(width: 4),
                            const Text(
                              'Jibu:',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: Color(0xFF6B7280),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          complain['response'] ?? '',
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.green.shade900,
                          ),
                        ),
                        if (complain['responded_by'] != null) ...[
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              Icon(Icons.person, size: 14, color: Colors.grey.shade600),
                              const SizedBox(width: 4),
                              Text(
                                'Na: ${complain['responded_by']}',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey.shade600,
                                ),
                              ),
                              if (complain['responded_at'] != null) ...[
                                const SizedBox(width: 8),
                                Text(
                                  '• ${complain['responded_at']}',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.grey.shade600,
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ],
                      ],
                    ),
                  ),
                ] else ...[
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.orange.shade50,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      children: [
                        Icon(Icons.access_time, color: Colors.orange.shade700, size: 16),
                        const SizedBox(width: 8),
                        const Text(
                          'Bado haijajibiwa',
                          style: TextStyle(
                            fontSize: 12,
                            color: Color(0xFF6B7280),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          );
        },
      ),
    );
  }
}
