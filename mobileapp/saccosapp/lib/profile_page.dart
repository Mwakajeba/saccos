import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'models/user_session.dart';
import 'services/api_service.dart';

class ProfilePage extends StatefulWidget {
  const ProfilePage({super.key});

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  bool _isIdVisible = false;
  File? _profileImage;
  final ImagePicker _picker = ImagePicker();

  Future<void> _pickImage() async {
    showDialog(
      context: context,
      builder: (context) => Dialog(
        backgroundColor: Colors.transparent,
        child: Container(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 40),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(10),
          ),
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            const SizedBox(height: 24),
            const Text(
              'Chagua Picha',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w700,
                color: Color(0xFF111813),
              ),
            ),
            const SizedBox(height: 24),
            ListTile(
              leading: Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFF13EC5B).withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.camera_alt,
                  color: Color(0xFF13EC5B),
                ),
              ),
              title: const Text(
                'Piga Picha',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                ),
              ),
              subtitle: Text(
                'Tumia kamera kupiga picha mpya',
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey.shade600,
                ),
              ),
              onTap: () async {
                Navigator.pop(context);
                final XFile? image = await _picker.pickImage(
                  source: ImageSource.camera,
                  maxWidth: 800,
                  maxHeight: 800,
                  imageQuality: 85,
                );
                if (image != null) {
                  setState(() {
                    _profileImage = File(image.path);
                  });
                  
                  // Upload to server
                  try {
                    final userId = UserSession.instance.userId;
                    if (userId != null) {
                      final result = await ApiService.uploadPhoto(
                        userId,
                        File(image.path),
                      );
                      
                      if (mounted && result['status'] == 200) {
                        // Update photo URL in session
                        UserSession.instance.photoUrl = result['photo_url'];
                        
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Picha imehifadhiwa kwenye server!'),
                            backgroundColor: Color(0xFF13EC5B),
                          ),
                        );
                      }
                    }
                  } catch (e) {
                    if (mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text('Picha imehifadhiwa kwenye simu, lakini kuna tatizo la mtandao: ${e.toString()}'),
                          backgroundColor: Colors.orange,
                        ),
                      );
                    }
                  }
                }
              },
            ),
            const SizedBox(height: 8),
            ListTile(
              leading: Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFF13EC5B).withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.photo_library,
                  color: Color(0xFF13EC5B),
                ),
              ),
              title: const Text(
                'Chagua kutoka Gallery',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                ),
              ),
              subtitle: Text(
                'Chagua picha iliyopo kwenye simu',
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey.shade600,
                ),
              ),
              onTap: () async {
                Navigator.pop(context);
                final XFile? image = await _picker.pickImage(
                  source: ImageSource.gallery,
                  maxWidth: 800,
                  maxHeight: 800,
                  imageQuality: 85,
                );
                if (image != null) {
                  setState(() {
                    _profileImage = File(image.path);
                  });
                  
                  // Upload to server
                  try {
                    final userId = UserSession.instance.userId;
                    if (userId != null) {
                      final result = await ApiService.uploadPhoto(
                        userId,
                        File(image.path),
                      );
                      
                      if (mounted && result['status'] == 200) {
                        // Update photo URL in session
                        UserSession.instance.photoUrl = result['photo_url'];
                        
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Picha imehifadhiwa kwenye server!'),
                            backgroundColor: Color(0xFF13EC5B),
                          ),
                        );
                      }
                    }
                  } catch (e) {
                    if (mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text('Picha imehifadhiwa kwenye simu, lakini kuna tatizo la mtandao: ${e.toString()}'),
                          backgroundColor: Colors.orange,
                        ),
                      );
                    }
                  }
                }
              },
            ),
            const SizedBox(height: 16),
          ],
        ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F8F6),
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
                // Scrollable Content
                Column(
                  children: [
                    // Top App Bar
                    Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFFF6F8F6).withOpacity(0.95),
                        border: Border(
                          bottom: BorderSide(
                            color: Colors.grey.shade200.withOpacity(0.5),
                          ),
                        ),
                      ),
                      padding: const EdgeInsets.fromLTRB(4, 16, 16, 12),
                      child: Row(
                        children: [
                          IconButton(
                            onPressed: () => Navigator.of(context).pop(),
                            icon: const Icon(
                              Icons.arrow_back_ios_new,
                              color: Color(0xFF111813),
                              size: 20,
                            ),
                          ),
                          const Expanded(
                            child: Text(
                              'Wasifu wa Mwanachama',
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
                    // Content
                    Expanded(
                      child: SingleChildScrollView(
                        padding: const EdgeInsets.only(bottom: 100),
                        child: Column(
                          children: [
                            // Profile Header
                            _buildProfileHeader(),
                            const SizedBox(height: 32),
                            // Info Section
                            _buildInfoSection(),
                            const SizedBox(height: 24),
                            // Footer Note
                            _buildFooterNote(),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
                // Bottom Button
                Positioned(
                  bottom: 0,
                  left: 0,
                  right: 0,
                  child: _buildBottomButton(),
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
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Column(
        children: [
          // Avatar with Camera Badge
          Stack(
            children: [
              Container(
                width: 128,
                height: 128,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.15),
                      blurRadius: 20,
                      offset: const Offset(0, 8),
                    ),
                  ],
                  border: Border.all(
                    color: Colors.white,
                    width: 4,
                  ),
                ),
                child: ClipOval(
                  child: _profileImage != null
                      ? Image.file(
                          _profileImage!,
                          fit: BoxFit.cover,
                          width: 128,
                          height: 128,
                        )
                      : (UserSession.instance.photoUrl != null && UserSession.instance.photoUrl!.isNotEmpty)
                          ? Image.network(
                              UserSession.instance.photoUrl!,
                              fit: BoxFit.cover,
                              width: 128,
                              height: 128,
                              errorBuilder: (context, error, stackTrace) {
                                return Container(
                                  color: const Color(0xFF13EC5B).withOpacity(0.1),
                                  child: const Icon(
                                    Icons.person,
                                    size: 64,
                                    color: Color(0xFF13EC5B),
                                  ),
                                );
                              },
                            )
                          : Container(
                              color: const Color(0xFF13EC5B).withOpacity(0.1),
                              child: const Icon(
                                Icons.person,
                                size: 64,
                                color: Color(0xFF13EC5B),
                              ),
                            ),
                ),
              ),
              Positioned(
                bottom: 4,
                right: 4,
                child: GestureDetector(
                  onTap: _pickImage,
                  child: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFF13EC5B),
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: const Color(0xFFF6F8F6),
                        width: 3,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.1),
                          blurRadius: 4,
                        ),
                      ],
                    ),
                    child: const Icon(
                      Icons.photo_camera,
                      size: 18,
                      color: Color(0xFF062915),
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          // Name with Verified Badge
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                UserSession.instance.name ?? 'Juma H. Mwaipopo',
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF111813),
                  letterSpacing: -0.3,
                ),
              ),
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.all(2),
                decoration: const BoxDecoration(
                  color: Color(0xFF13EC5B),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.check,
                  size: 14,
                  color: Colors.white,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          // Status Badge
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: const Color(0xFF13EC5B).withOpacity(0.1),
              borderRadius: BorderRadius.circular(100),
            ),
            child: const Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  Icons.circle,
                  size: 6,
                  color: Color(0xFF13EC5B),
                ),
                SizedBox(width: 6),
                Text(
                  'Mwanachama Hai',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF0A4521),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoSection() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(left: 16, bottom: 8),
            child: Text(
              'TAARIFA BINAFSI',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: Colors.grey.shade500,
                letterSpacing: 1,
              ),
            ),
          ),
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
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
              children: [
                _buildInfoItem(
                  icon: Icons.call,
                  label: 'Namba ya Simu',
                  value: UserSession.instance.phone ?? '+255 000 000 000',
                  showDivider: true,
                ),
                _buildInfoItem(
                  icon: Icons.location_on_outlined,
                  label: 'Tawi',
                  value: UserSession.instance.branch ?? 'Haijawekwa',
                  showDivider: true,
                ),
                _buildInfoItem(
                  icon: Icons.group_outlined,
                  label: 'Kikundi',
                  value: UserSession.instance.groupName ?? 'Haijawekwa',
                  showDivider: true,
                ),
                _buildInfoItem(
                  icon: Icons.badge_outlined,
                  label: 'Namba ya Uanachama',
                  value: UserSession.instance.memberNo ?? 'Haijawekwa',
                  showDivider: true,
                ),
                _buildInfoItem(
                  icon: Icons.person_outline,
                  label: 'Jinsia',
                  value: UserSession.instance.gender == 'M' ? 'Mwanaume' : UserSession.instance.gender == 'F' ? 'Mwanamke' : 'Haijawekwa',
                  showDivider: true,
                ),
                _buildInfoItem(
                  icon: Icons.fingerprint,
                  label: 'Namba ya Mtumiaji',
                  value: UserSession.instance.userId?.toString() ?? 'Haijawekwa',
                  showDivider: false,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoItem({
    required IconData icon,
    required String label,
    required String value,
    required bool showDivider,
    Widget? trailing,
  }) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: const Color(0xFF13EC5B).withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  icon,
                  size: 20,
                  color: const Color(0xFF0EB545),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      label,
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w500,
                        color: Colors.grey.shade500,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      value,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF111813),
                      ),
                    ),
                  ],
                ),
              ),
              if (trailing != null) trailing,
            ],
          ),
        ),
        if (showDivider)
          Padding(
            padding: const EdgeInsets.only(left: 72),
            child: Divider(
              height: 1,
              color: Colors.grey.shade100,
              thickness: 1,
            ),
          ),
      ],
    );
  }

  Widget _buildFooterNote() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 32),
      child: Text(
        'Taarifa hizi hutumika kuthibitisha umiliki wako wa hisa na akiba ndani ya kikundi. Hakikisha ni sahihi wakati wote.',
        textAlign: TextAlign.center,
        style: TextStyle(
          fontSize: 12,
          color: Colors.grey.shade400,
          height: 1.5,
        ),
      ),
    );
  }

  Widget _buildBottomButton() {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFFF6F8F6).withOpacity(0.8),
        border: Border(
          top: BorderSide(
            color: Colors.grey.shade200.withOpacity(0.5),
          ),
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
              // Handle edit
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF13EC5B),
              foregroundColor: const Color(0xFF062915),
              elevation: 8,
              shadowColor: const Color(0xFF13EC5B).withOpacity(0.2),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: const Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.edit_square, size: 20),
                SizedBox(width: 8),
                Text(
                  'Sasisha Taarifa',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 0.5,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
