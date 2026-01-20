import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:file_picker/file_picker.dart';
import 'package:url_launcher/url_launcher.dart';

import 'models/user_session.dart';
import 'services/api_service.dart';

class LoanDocumentPage extends StatefulWidget {
  final Map<String, dynamic> loanData;

  const LoanDocumentPage({
    super.key,
    required this.loanData,
  });

  @override
  State<LoanDocumentPage> createState() => _LoanDocumentPageState();
}

class _LoanDocumentPageState extends State<LoanDocumentPage> {
  bool _loading = false;
  bool _uploading = false;
  String? _error;

  List<dynamic> _filetypes = [];
  List<dynamic> _documents = [];

  int? _selectedFiletypeId;
  File? _selectedFile;

  @override
  void initState() {
    super.initState();
    _loadAll();
  }

  int _loanId() => (widget.loanData['id'] as num).toInt();

  String _loanTitle() => (widget.loanData['product_name'] ?? widget.loanData['product'] ?? 'Mkopo').toString();

  Future<void> _loadAll() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final customerId = UserSession.instance.userId;
      if (customerId == null) throw Exception('Tafadhali ingia tena');

      final ftResp = await ApiService.getFiletypes();
      if (ftResp['status'] == 200) {
        _filetypes = ftResp['filetypes'] ?? [];
      }

      final docsResp = await ApiService.getLoanDocuments(
        customerId: customerId,
        loanId: _loanId(),
      );
      if (docsResp['status'] == 200) {
        _documents = docsResp['documents'] ?? [];
      }

      // default select first filetype
      if (_filetypes.isNotEmpty && _selectedFiletypeId == null) {
        _selectedFiletypeId = (_filetypes.first['id'] as num).toInt();
      }
    } catch (e) {
      setState(() {
        _error = e.toString().replaceFirst('Exception:', '').trim();
      });
    } finally {
      setState(() => _loading = false);
    }
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final picker = ImagePicker();
      final picked = await picker.pickImage(
        source: source,
        imageQuality: 85,
      );
      if (picked == null) return;

      setState(() {
        _selectedFile = File(picked.path);
      });
    } catch (e) {
      _showSnack('Imeshindwa kuchagua picha');
    }
  }

  Future<void> _pickDocument() async {
    try {
      FilePickerResult? result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['pdf', 'doc', 'docx'],
        allowMultiple: false,
      );

      if (result != null && result.files.single.path != null) {
        setState(() {
          _selectedFile = File(result.files.single.path!);
        });
      }
    } catch (e) {
      _showSnack('Imeshindwa kuchagua nyaraka');
    }
  }

  Future<void> _upload() async {
    if (_selectedFiletypeId == null) {
      _showSnack('Chagua aina ya nyaraka');
      return;
    }
    if (_selectedFile == null) {
      _showSnack('Chagua faili kwanza');
      return;
    }

    setState(() {
      _uploading = true;
      _error = null;
    });

    try {
      final customerId = UserSession.instance.userId;
      if (customerId == null) throw Exception('Tafadhali ingia tena');

      final resp = await ApiService.uploadLoanDocument(
        customerId: customerId,
        loanId: _loanId(),
        fileTypeId: _selectedFiletypeId!,
        file: _selectedFile!,
      );

      if (resp['status'] == 200) {
        _showSnack('Nyaraka imepakiwa');
        setState(() {
          _selectedFile = null;
        });
        await _loadAll();
      } else {
        throw Exception(resp['message'] ?? 'Imeshindwa kupakia nyaraka');
      }
    } catch (e) {
      setState(() {
        _error = e.toString().replaceFirst('Exception:', '').trim();
      });
      _showSnack(_error ?? 'Imeshindwa kupakia nyaraka');
    } finally {
      setState(() => _uploading = false);
    }
  }

  Future<void> _openUrl(String url) async {
    final uri = Uri.tryParse(url);
    if (uri == null) return;
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      _showSnack('Imeshindwa kufungua nyaraka');
    }
  }

  void _showSnack(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F8F6),
      appBar: AppBar(
        title: Text('Weka/Ona KYC - ${_loanTitle()}'),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF111813),
        elevation: 1,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadAll,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Upload card
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFDBE6DF)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Pakia Nyaraka (KYC)',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF111813),
                            ),
                          ),
                          const SizedBox(height: 12),
                          if (_filetypes.isEmpty)
                            const Text('Hakuna aina za nyaraka (filetypes) zilizoandaliwa.'),
                          if (_filetypes.isNotEmpty)
                            DropdownButtonFormField<int>(
                              value: _selectedFiletypeId,
                              items: _filetypes.map<DropdownMenuItem<int>>((ft) {
                                return DropdownMenuItem<int>(
                                  value: (ft['id'] as num).toInt(),
                                  child: Text((ft['name'] ?? '').toString()),
                                );
                              }).toList(),
                              onChanged: (v) => setState(() => _selectedFiletypeId = v),
                              decoration: const InputDecoration(
                                labelText: 'Aina ya nyaraka',
                                border: OutlineInputBorder(),
                              ),
                            ),
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              Expanded(
                                child: OutlinedButton.icon(
                                  onPressed: _uploading ? null : () => _pickImage(ImageSource.gallery),
                                  icon: const Icon(Icons.photo_library_outlined),
                                  label: const Text('Chagua Picha'),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: OutlinedButton.icon(
                                  onPressed: _uploading ? null : () => _pickImage(ImageSource.camera),
                                  icon: const Icon(Icons.photo_camera_outlined),
                                  label: const Text('Piga Picha'),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          SizedBox(
                            width: double.infinity,
                            child: OutlinedButton.icon(
                              onPressed: _uploading ? null : _pickDocument,
                              icon: const Icon(Icons.insert_drive_file_outlined),
                              label: const Text('Chagua PDF/DOC'),
                            ),
                          ),
                          const SizedBox(height: 12),
                          if (_selectedFile != null)
                            Text(
                              'Imechaguliwa: ${_selectedFile!.path.split('/').last}',
                              style: const TextStyle(fontWeight: FontWeight.w600),
                            ),
                          const SizedBox(height: 12),
                          SizedBox(
                            width: double.infinity,
                            height: 48,
                            child: ElevatedButton(
                              onPressed: _uploading ? null : _upload,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF13EC5B),
                                foregroundColor: const Color(0xFF052E16),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              child: _uploading
                                  ? const SizedBox(
                                      width: 20,
                                      height: 20,
                                      child: CircularProgressIndicator(strokeWidth: 2),
                                    )
                                  : const Text(
                                      'Pakia',
                                      style: TextStyle(fontWeight: FontWeight.w800),
                                    ),
                            ),
                          ),
                          if (_error != null) ...[
                            const SizedBox(height: 12),
                            Text(
                              _error!,
                              style: const TextStyle(color: Colors.red),
                            ),
                          ],
                        ],
                      ),
                    ),

                    const SizedBox(height: 16),

                    // Documents list
                    const Text(
                      'Nyaraka Zilizopakiwa',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF111813),
                      ),
                    ),
                    const SizedBox(height: 8),
                    if (_documents.isEmpty)
                      const Text('Bado hujapakia nyaraka yoyote.'),
                    if (_documents.isNotEmpty)
                      ..._documents.map((doc) {
                        final typeName = (doc['file_type'] ?? 'Nyaraka').toString();
                        final url = (doc['url'] ?? '').toString();
                        return Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFDBE6DF)),
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 40,
                                height: 40,
                                decoration: BoxDecoration(
                                  color: const Color(0xFF13EC5B).withOpacity(0.1),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.description_outlined, color: Color(0xFF13EC5B)),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      typeName,
                                      style: const TextStyle(fontWeight: FontWeight.w800),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      (doc['created_at'] ?? '').toString(),
                                      style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280)),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 12),
                              OutlinedButton(
                                onPressed: url.isEmpty ? null : () => _openUrl(url),
                                child: const Text('Ona'),
                              ),
                            ],
                          ),
                        );
                      }),
                  ],
                ),
              ),
            ),
    );
  }
}

