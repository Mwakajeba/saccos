import 'dart:convert';
import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:photo_view/photo_view.dart';
import 'package:http/http.dart' as http;

class DocumentPreviewPage extends StatefulWidget {
  final String url;
  final String fileName;
  final String fileType;

  const DocumentPreviewPage({
    super.key,
    required this.url,
    required this.fileName,
    required this.fileType,
  });

  @override
  State<DocumentPreviewPage> createState() => _DocumentPreviewPageState();
}

class _DocumentPreviewPageState extends State<DocumentPreviewPage> {
  bool _isLoading = true;
  String? _error;
  Uint8List? _fileBytes;
  bool _isPdf = false;

  @override
  void initState() {
    super.initState();
    _loadDocument();
  }

  Future<void> _loadDocument() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      // Check if it's a PDF based on URL or file type
      final urlLower = widget.url.toLowerCase();
      final fileNameLower = widget.fileName.toLowerCase();
      _isPdf = urlLower.endsWith('.pdf') || 
                fileNameLower.endsWith('.pdf') ||
                widget.fileType.toLowerCase().contains('pdf');

      if (_isPdf) {
        // For PDFs, we'll load directly in webview, no need to download
        setState(() {
          _isLoading = false;
        });
      } else {
        // For images, download to display with PhotoView
        final response = await http.get(Uri.parse(widget.url));
        
        if (response.statusCode == 200) {
          setState(() {
            _fileBytes = response.bodyBytes;
            _isLoading = false;
          });
        } else {
          throw Exception('Failed to load document: ${response.statusCode}');
        }
      }
    } catch (e) {
      setState(() {
        _error = e.toString().replaceFirst('Exception:', '').trim();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: Text(
          widget.fileName,
          style: const TextStyle(color: Colors.white),
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
        backgroundColor: Colors.black,
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          IconButton(
            icon: const Icon(Icons.download),
            onPressed: _fileBytes != null
                ? () {
                    // You can add download functionality here if needed
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Download functionality coming soon')),
                    );
                  }
                : null,
            tooltip: 'Download',
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: Colors.white),
            )
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, color: Colors.red, size: 48),
                      const SizedBox(height: 16),
                      Text(
                        'Imeshindwa kuonyesha nyaraka',
                        style: const TextStyle(color: Colors.white, fontSize: 16),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        _error!,
                        style: const TextStyle(color: Colors.grey, fontSize: 12),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadDocument,
                        child: const Text('Jaribu Tena'),
                      ),
                    ],
                  ),
                )
              : _fileBytes != null
                  ? _isPdf
                      ? _buildPdfViewer()
                      : _buildImageViewer()
                  : const Center(
                      child: Text(
                        'Hakuna data ya nyaraka',
                        style: TextStyle(color: Colors.white),
                      ),
                    ),
    );
  }

  Widget _buildPdfViewer() {
    // Load PDF directly from URL in webview
    // Most browsers can display PDFs natively
    final controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(Colors.black)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (String url) {
            if (mounted) {
              setState(() {
                _isLoading = true;
              });
            }
          },
          onPageFinished: (String url) {
            if (mounted) {
              setState(() {
                _isLoading = false;
              });
            }
          },
          onWebResourceError: (WebResourceError error) {
            if (mounted) {
              setState(() {
                _error = 'Failed to load PDF: ${error.description}';
                _isLoading = false;
              });
            }
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.url));

    return Stack(
      children: [
        WebViewWidget(controller: controller),
        if (_isLoading)
          const Center(
            child: CircularProgressIndicator(color: Colors.white),
          ),
      ],
    );
  }

  Widget _buildImageViewer() {
    return PhotoView(
      imageProvider: MemoryImage(_fileBytes!),
      minScale: PhotoViewComputedScale.contained,
      maxScale: PhotoViewComputedScale.covered * 2,
      backgroundDecoration: const BoxDecoration(color: Colors.black),
      loadingBuilder: (context, event) => const Center(
        child: CircularProgressIndicator(color: Colors.white),
      ),
      errorBuilder: (context, error, stackTrace) => Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, color: Colors.red, size: 48),
            const SizedBox(height: 16),
            const Text(
              'Imeshindwa kuonyesha picha',
              style: TextStyle(color: Colors.white),
            ),
          ],
        ),
      ),
    );
  }
}
