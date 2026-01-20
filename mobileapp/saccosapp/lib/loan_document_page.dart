import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:file_picker/file_picker.dart';

import 'models/user_session.dart';
import 'services/api_service.dart';
import 'document_preview_page.dart';

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
    print('=== LOAN DOCUMENT PAGE INIT ===');
    print('Loan Data: ${widget.loanData}');
    print('Loan Data Keys: ${widget.loanData.keys.toList()}');
    print('Loan ID: ${widget.loanData['id']}');
    _loadAll();
  }

  int _loanId() {
    // The API returns 'loanid' not 'id' - check both for compatibility
    final id = widget.loanData['loanid'] ?? widget.loanData['id'];
    if (id == null) {
      print('ERROR: Loan ID not found in loanData');
      print('Available keys: ${widget.loanData.keys.toList()}');
      print('Loan data: ${widget.loanData}');
      throw Exception('Loan ID is missing. Available keys: ${widget.loanData.keys.toList()}');
    }
    if (id is num) {
      return id.toInt();
    }
    if (id is String) {
      final parsed = int.tryParse(id);
      if (parsed == null) {
        throw Exception('Loan ID is not a valid number: $id');
      }
      return parsed;
    }
    throw Exception('Loan ID has invalid type: ${id.runtimeType}');
  }

  String _loanTitle() => (widget.loanData['product_name'] ?? widget.loanData['product'] ?? 'Mkopo').toString();

  Future<void> _loadAll() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final customerId = UserSession.instance.userId;
      if (customerId == null) throw Exception('Tafadhali ingia tena');

      // Load filetypes
      try {
        final ftResp = await ApiService.getFiletypes();
        print('=== FILETYPES API RESPONSE ===');
        print('Status: ${ftResp['status']}');
        print('Filetypes: ${ftResp['filetypes']}');
        print('Response keys: ${ftResp.keys.toList()}');
        
        final status = ftResp['status'];
        if (status == 200 || status == '200') {
          final filetypesList = ftResp['filetypes'];
          if (filetypesList != null && filetypesList is List) {
            _filetypes = filetypesList;
            print('Loaded ${_filetypes.length} filetypes');
          } else {
            print('WARNING: filetypes is not a list or is null. Type: ${filetypesList.runtimeType}');
            _filetypes = [];
          }
        } else {
          print('WARNING: API returned status $status (type: ${status.runtimeType})');
          _filetypes = [];
        }
      } catch (e) {
        print('ERROR loading filetypes: $e');
        _filetypes = [];
      }

      // Load documents
      try {
        final loanId = _loanId();
        if (loanId > 0) {
          final docsResp = await ApiService.getLoanDocuments(
            customerId: customerId,
            loanId: loanId,
          );
          final status = docsResp['status'];
          if (status == 200 || status == '200') {
            _documents = docsResp['documents'] ?? [];
          } else {
            print('WARNING: Documents API returned status $status');
            _documents = [];
          }
        } else {
          print('WARNING: Invalid loan ID: $loanId');
          _documents = [];
        }
      } catch (e) {
        print('ERROR loading documents: $e');
        _documents = [];
      }

      // default select first filetype
      if (_filetypes.isNotEmpty && _selectedFiletypeId == null) {
        _selectedFiletypeId = (_filetypes.first['id'] as num).toInt();
      }
      
      print('=== FINAL STATE ===');
      print('Filetypes count: ${_filetypes.length}');
      print('Selected filetype ID: $_selectedFiletypeId');
    } catch (e) {
      String errorMsg = e.toString().replaceFirst('Exception:', '').trim();
      // Clean up error messages
      if (errorMsg.contains('SERVER_ERROR:404')) {
        errorMsg = 'Huduma haipatikani (404). Tafadhali jaribu tena baadaye.';
      } else if (errorMsg.contains('SERVER_ERROR:')) {
        errorMsg = 'Tatizo la seva. Tafadhali jaribu tena.';
      } else if (errorMsg.contains('NETWORK_ERROR')) {
        errorMsg = 'Hakuna muunganisho wa mtandao. Angalia muunganisho wako.';
      } else if (errorMsg.contains('TIMEOUT')) {
        errorMsg = 'Muda umekwisha. Tafadhali jaribu tena.';
      }
      setState(() {
        _error = errorMsg;
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
      if (customerId == null) {
        throw Exception('Tafadhali ingia tena');
      }

      // Get and validate loan ID
      int loanId;
      try {
        loanId = _loanId();
        print('=== UPLOAD DEBUG ===');
        print('Customer ID: $customerId');
        print('Loan ID: $loanId');
        print('File Type ID: $_selectedFiletypeId');
        print('File: ${_selectedFile?.path}');
        print('Loan Data: ${widget.loanData}');
        
        if (loanId <= 0) {
          throw Exception('Loan ID is missing or invalid. Loan Data: ${widget.loanData}');
        }
      } catch (e) {
        print('ERROR getting loan ID: $e');
        throw Exception('Loan ID is missing. Please try again or contact support.');
      }

      final resp = await ApiService.uploadLoanDocument(
        customerId: customerId,
        loanId: loanId,
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
      String errorMsg = e.toString().replaceFirst('Exception:', '').trim();
      // Clean up error messages
      if (errorMsg.contains('SERVER_ERROR:404') || errorMsg.contains('UPLOAD_FAILED:404')) {
        errorMsg = 'Huduma haipatikani (404). Tafadhali jaribu tena baadaye.';
      } else if (errorMsg.contains('SERVER_ERROR:') || errorMsg.contains('UPLOAD_FAILED:')) {
        errorMsg = 'Tatizo la seva. Tafadhali jaribu tena.';
      } else if (errorMsg.contains('NETWORK_ERROR')) {
        errorMsg = 'Hakuna muunganisho wa mtandao. Angalia muunganisho wako.';
      } else if (errorMsg.contains('TIMEOUT')) {
        errorMsg = 'Muda umekwisha. Tafadhali jaribu tena.';
      }
      setState(() {
        _error = errorMsg;
      });
      _showSnack(_error ?? 'Imeshindwa kupakia nyaraka');
    } finally {
      setState(() => _uploading = false);
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
                          DropdownButtonFormField<int>(
                            value: _selectedFiletypeId,
                            items: _filetypes.isEmpty
                                ? [
                                    const DropdownMenuItem<int>(
                                      value: null,
                                      enabled: false,
                                      child: Text('Hakuna aina za nyaraka'),
                                    )
                                  ]
                                : _filetypes.map<DropdownMenuItem<int>>((ft) {
                                    return DropdownMenuItem<int>(
                                      value: (ft['id'] as num).toInt(),
                                      child: Text((ft['name'] ?? '').toString()),
                                    );
                                  }).toList(),
                            onChanged: _filetypes.isEmpty ? null : (v) => setState(() => _selectedFiletypeId = v),
                            decoration: const InputDecoration(
                              labelText: 'Aina ya nyaraka *',
                              border: OutlineInputBorder(),
                              hintText: 'Chagua aina ya nyaraka',
                            ),
                            isExpanded: true,
                          ),
                          if (_filetypes.isEmpty)
                            Padding(
                              padding: const EdgeInsets.only(top: 8),
                              child: Text(
                                'Hakuna aina za nyaraka zilizoandaliwa. Tafadhali wasiliana na msimamizi.',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.orange.shade700,
                                  fontStyle: FontStyle.italic,
                                ),
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
                        final fileName = url.split('/').last.isNotEmpty 
                            ? url.split('/').last 
                            : '${typeName}_${doc['id'] ?? ''}';
                        
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
                                onPressed: url.isEmpty
                                    ? null
                                    : () {
                                        Navigator.push(
                                          context,
                                          MaterialPageRoute(
                                            builder: (context) => DocumentPreviewPage(
                                              url: url,
                                              fileName: fileName,
                                              fileType: typeName,
                                            ),
                                          ),
                                        );
                                      },
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

