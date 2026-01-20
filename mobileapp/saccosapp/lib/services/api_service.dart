import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;

class ApiService {
  // For Linux/Windows/Mac desktop use: 127.0.0.1
  // For Android Emulator use: 10.0.2.2
  // For physical device/iOS use: 192.168.1.193 (your computer's IP)
  //static const String baseUrl = 'http://127.0.0.1:8001/api';
  //static const String baseUrl = 'http://10.0.2.2:8000/api'; // For Android Emulator
  //static const String baseUrl = 'http://192.168.1.193:8000/api'; // For physical device
  static const String baseUrl = 'https://epm.smartsoft.co.tz/api';
  
  // Login API
  static Future<Map<String, dynamic>> login(String username, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/customer/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'username': username,
          'password': password,
        }),
      ).timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        print('=== LOGIN RESPONSE ===');
        print(jsonEncode(data));
        print('======================');
        return data;
      } else if (response.statusCode == 401 || response.statusCode == 422) {
        final errorData = jsonDecode(response.body);
        //throw Exception('AUTH_ERROR:${errorData['message'] ?? 'Simu au nywila si sahihi'}');
        throw Exception('Simu au neno la siri si sahihi');
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Get customer profile
  static Future<Map<String, dynamic>> getProfile(int customerId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/customer/profile'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'customer_id': customerId,
        }),
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Get customer loans
  static Future<Map<String, dynamic>> getLoans(int customerId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/customer/loans'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'customer_id': customerId,
        }),
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Get filetypes for KYC / loan documents
  static Future<Map<String, dynamic>> getFiletypes() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/customer/filetypes'),
        headers: {
          'Accept': 'application/json',
        },
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // List loan documents for a loan
  static Future<Map<String, dynamic>> getLoanDocuments({
    required int customerId,
    required int loanId,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/customer/loan-documents'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'customer_id': customerId,
          'loan_id': loanId,
        }),
      ).timeout(
        const Duration(seconds: 20),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        // Bubble server message if any
        try {
          final data = jsonDecode(response.body);
          throw Exception(data['message'] ?? 'SERVER_ERROR:${response.statusCode}');
        } catch (_) {
          throw Exception('SERVER_ERROR:${response.statusCode}');
        }
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Upload a single loan document (multipart)
  static Future<Map<String, dynamic>> uploadLoanDocument({
    required int customerId,
    required int loanId,
    required int fileTypeId,
    required File file,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/customer/loan-documents/upload');
      final request = http.MultipartRequest('POST', uri);

      request.fields['customer_id'] = customerId.toString();
      request.fields['loan_id'] = loanId.toString();
      request.fields['file_type_id'] = fileTypeId.toString();

      request.files.add(await http.MultipartFile.fromPath('file', file.path));
      request.headers['Accept'] = 'application/json';

      final streamed = await request.send().timeout(
        const Duration(seconds: 45),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );
      final response = await http.Response.fromStream(streamed);

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        try {
          final data = jsonDecode(response.body);
          throw Exception(data['message'] ?? 'UPLOAD_FAILED:${response.statusCode}');
        } catch (_) {
          throw Exception('UPLOAD_FAILED:${response.statusCode}');
        }
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Get group members
  static Future<Map<String, dynamic>> getGroupMembers(int customerId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/customer/group-members'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'customer_id': customerId,
        }),
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Get loan products
  static Future<Map<String, dynamic>> getLoanProducts() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/customer/loan-products'),
        headers: {
          'Accept': 'application/json',
        },
      ).timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        print('=== LOAN PRODUCTS RESPONSE ===');
        print(jsonEncode(data));
        print('==============================');
        return data;
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Upload customer photo
  static Future<Map<String, dynamic>> uploadPhoto(int customerId, File photoFile) async {
    try {
      var request = http.MultipartRequest(
        'POST',
        Uri.parse('$baseUrl/customer/update-photo'),
      );

      // Add customer_id field
      request.fields['customer_id'] = customerId.toString();

      // Add photo file
      request.files.add(
        await http.MultipartFile.fromPath(
          'photo',
          photoFile.path,
        ),
      );

      final streamedResponse = await request.send().timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        print('=== UPLOAD PHOTO RESPONSE ===');
        print(jsonEncode(data));
        print('=============================');
        return data;
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Get customer contributions
  static Future<Map<String, dynamic>> getContributions(int customerId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/customer/contributions'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'customer_id': customerId,
        }),
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Get customer shares
  static Future<Map<String, dynamic>> getShares(int customerId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/customer/shares'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'customer_id': customerId,
        }),
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Get contribution transactions
  static Future<Map<String, dynamic>> getContributionTransactions(int accountId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/customer/contribution-transactions'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'account_id': accountId,
        }),
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Get share transactions
  static Future<Map<String, dynamic>> getShareTransactions(int accountId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/customer/share-transactions'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'account_id': accountId,
        }),
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Submit loan application
  static Future<Map<String, dynamic>> submitLoanApplication(Map<String, dynamic> applicationData) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/customer/loan-application'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode(applicationData),
      ).timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else if (response.statusCode == 400 || response.statusCode == 422) {
        final errorData = jsonDecode(response.body);
        throw Exception(errorData['message'] ?? 'Tatizo katika kuwasilisha ombi la mkopo');
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Get complain categories
  static Future<Map<String, dynamic>> getComplainCategories() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/customer/complain-categories'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Submit complain
  static Future<Map<String, dynamic>> submitComplain(Map<String, dynamic> complainData) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/customer/complain'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode(complainData),
      ).timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else if (response.statusCode == 400 || response.statusCode == 422) {
        final errorData = jsonDecode(response.body);
        throw Exception(errorData['message'] ?? 'Tatizo katika kuwasilisha malalamiko');
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Get customer complains
  static Future<Map<String, dynamic>> getCustomerComplains(int customerId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/customer/complains'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'customer_id': customerId,
        }),
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Get Next of Kin
  static Future<Map<String, dynamic>> getNextOfKin(String userId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/customer/next-of-kin'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
        }),
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          throw Exception('TIMEOUT');
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException {
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }

  // Get Announcements
  static Future<Map<String, dynamic>> getAnnouncements(String customerId) async {
    try {
      print('=== CALLING ANNOUNCEMENTS API ===');
      print('URL: $baseUrl/customer/announcements');
      print('Customer ID: $customerId');
      
      final response = await http.post(
        Uri.parse('$baseUrl/customer/announcements'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'customer_id': customerId,
        }),
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          print('=== ANNOUNCEMENTS API TIMEOUT ===');
          throw Exception('TIMEOUT');
        },
      );

      print('=== ANNOUNCEMENTS API RESPONSE ===');
      print('Status Code: ${response.statusCode}');
      print('Response Body: ${response.body}');
      print('===================================');

      if (response.statusCode == 200) {
        final decoded = jsonDecode(response.body);
        return decoded;
      } else {
        print('=== ANNOUNCEMENTS API ERROR ===');
        print('Status: ${response.statusCode}');
        print('Body: ${response.body}');
        throw Exception('SERVER_ERROR:${response.statusCode}');
      }
    } on http.ClientException catch (e) {
      print('=== ANNOUNCEMENTS API CLIENT EXCEPTION ===');
      print('Error: $e');
      throw Exception('NETWORK_ERROR');
    } catch (e) {
      print('=== ANNOUNCEMENTS API EXCEPTION ===');
      print('Error: $e');
      if (e.toString().contains('Exception:')) {
        rethrow;
      }
      throw Exception('NETWORK_ERROR');
    }
  }
}
