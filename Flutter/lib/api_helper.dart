import 'dart:convert';
import 'package:http/http.dart' as http;

class APIHelper {
  static final APIHelper _instance = APIHelper._init();
  static const String _baseUrl =
      'https://honeydew-panther-755692.hostingersite.com/api.php';

  APIHelper._init();

  factory APIHelper() {
    return _instance;
  }

  Future<List<Map<String, dynamic>>> getAllProducts() async {
    final response = await http.get(Uri.parse(_baseUrl));
    if (response.statusCode == 200) {
      List data = json.decode(response.body);
      return data.map((e) => Map<String, dynamic>.from(e)).toList();
    } else {
      throw Exception('Failed to load products');
    }
  }
}
