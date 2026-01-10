import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl = 'https://dev.smartsoft.co.tz/api';
  
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
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('Failed to load profile');
      }
    } catch (e) {
      throw Exception('Network error: ${e.toString()}');
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
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('Failed to load loans');
      }
    } catch (e) {
      throw Exception('Network error: ${e.toString()}');
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
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('Failed to load group members');
      }
    } catch (e) {
      throw Exception('Network error: ${e.toString()}');
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
}
