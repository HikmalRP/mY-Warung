import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'halaman_dashboard.dart';
import 'halaman_register.dart';

class HalamanLogin extends StatefulWidget {
  final SharedPreferences spInstance;

  const HalamanLogin(this.spInstance, {Key? key}) : super(key: key);

  @override
  State<StatefulWidget> createState() => _HalamanLoginState();
}

class _HalamanLoginState extends State<HalamanLogin> {
  TextEditingController usernameController = TextEditingController();
  TextEditingController passwordController = TextEditingController();
  bool showPassword = false;

  void toggleShowPassword() {
    setState(() {
      showPassword = !showPassword;
    });
  }

  Future<void> _login() async {
    // Validasi input kosong
    if (usernameController.text.isEmpty || passwordController.text.isEmpty) {
      ScaffoldMessenger.of(context).clearSnackBars();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text(
            "Username dan password harus diisi",
            style: TextStyle(color: Colors.white),
          ),
          backgroundColor: Colors.orange,
          action: SnackBarAction(
            label: "Tutup",
            textColor: Colors.white,
            onPressed: () {
              ScaffoldMessenger.of(context).removeCurrentSnackBar();
            },
          ),
        ),
      );
      return;
    }

    // Kirim permintaan ke API login
    final response = await http.post(
      Uri.parse('https://honeydew-panther-755692.hostingersite.com//login.php'),
      body: {
        'username': usernameController.text,
        'password': passwordController.text,
      },
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      if (data['status'] == 'success') {
        // Simpan username ke SharedPreferences
        await widget.spInstance.setString('currentUsername', data['username']);

        // Navigasi ke Dashboard
        Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(
            builder: (context) => HalamanDashboard(
              widget.spInstance,
              data['username'],
            ),
          ),
          (route) => false,
        );
      } else {
        _showErrorSnackbar(data['message']);
      }
    } else {
      _showErrorSnackbar("Terjadi kesalahan server");
    }
  }

  void _showErrorSnackbar(String message) {
    ScaffoldMessenger.of(context).clearSnackBars();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          message,
          style: const TextStyle(color: Colors.white),
        ),
        backgroundColor: Colors.red,
        action: SnackBarAction(
          label: "Tutup",
          textColor: Colors.white,
          onPressed: () {
            ScaffoldMessenger.of(context).removeCurrentSnackBar();
          },
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Login"),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Center(
          child: Container(
            constraints: const BoxConstraints(maxWidth: 500),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 24),
                TextField(
                  controller: usernameController,
                  decoration: const InputDecoration(
                    icon: Icon(Icons.person),
                    labelText: "Username",
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: passwordController,
                  obscureText: !showPassword,
                  decoration: InputDecoration(
                    icon: const Icon(Icons.key),
                    labelText: "Password",
                    suffixIcon: GestureDetector(
                      onTap: toggleShowPassword,
                      child: Icon(
                        showPassword ? Icons.visibility : Icons.visibility_off,
                      ),
                    ),
                    border: const OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 24),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: _login,
                        icon: const Icon(Icons.login),
                        label: const Text("Login"),
                        style: ElevatedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 16.0),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Center(
                  child: TextButton(
                    onPressed: () {
                      // Navigasi ke HalamanRegistrasi
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) =>
                              HalamanRegistrasi(widget.spInstance),
                        ),
                      );
                    },
                    child: const Text(
                      "Registrasi",
                      style: TextStyle(fontSize: 16),
                    ),
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
