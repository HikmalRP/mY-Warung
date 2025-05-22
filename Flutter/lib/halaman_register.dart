import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'halaman_login.dart';

class HalamanRegistrasi extends StatefulWidget {
  final SharedPreferences spInstance;

  const HalamanRegistrasi(this.spInstance, {Key? key}) : super(key: key);

  @override
  State<StatefulWidget> createState() => _HalamanRegistrasiState();
}

class _HalamanRegistrasiState extends State<HalamanRegistrasi> {
  TextEditingController usernameController = TextEditingController();
  TextEditingController passwordController = TextEditingController();
  TextEditingController konfirmasiPasswordController = TextEditingController();
  bool showPassword = false, showKonfirmasiPassword = false;
  String? usernameError, passwordError, konfirmasiPasswordError;

  void toggleShowPassword() {
    setState(() {
      showPassword = !showPassword;
    });
  }

  void toggleShowKonfirmasiPassword() {
    setState(() {
      showKonfirmasiPassword = !showKonfirmasiPassword;
    });
  }

  void validasiUsername() {
    if (usernameController.text.isEmpty) {
      setState(() {
        usernameError = "Username tidak boleh kosong";
      });
      return;
    }
    setState(() {
      usernameError = null;
    });
  }

  void validasiPassword() {
    if (passwordController.text.isEmpty) {
      setState(() {
        passwordError = "Password tidak boleh kosong";
      });
      return;
    }
    if (passwordController.text.length < 8) {
      setState(() {
        passwordError = "Password harus minimal 8 karakter";
      });
      return;
    }
    if (!RegExp(r"[A-Z]").hasMatch(passwordController.text)) {
      setState(() {
        passwordError = "Password harus mengandung huruf besar";
      });
      return;
    }
    if (!RegExp(r"[a-z]").hasMatch(passwordController.text)) {
      setState(() {
        passwordError = "Password harus mengandung huruf kecil";
      });
      return;
    }
    if (!RegExp(r"[0-9]").hasMatch(passwordController.text)) {
      setState(() {
        passwordError = "Password harus mengandung angka";
      });
      return;
    }
    setState(() {
      passwordError = null;
    });
  }

  void validasiKonfirmasiPassword() {
    if (konfirmasiPasswordController.text.isEmpty) {
      setState(() {
        konfirmasiPasswordError = "Konfirmasi password tidak boleh kosong";
      });
      return;
    }
    if (konfirmasiPasswordController.text != passwordController.text) {
      setState(() {
        konfirmasiPasswordError = "Password tidak sama";
      });
      return;
    }
    setState(() {
      konfirmasiPasswordError = null;
    });
  }

  Future<void> _register() async {
    validasiUsername();
    validasiPassword();
    validasiKonfirmasiPassword();

    if (usernameError == null &&
        passwordError == null &&
        konfirmasiPasswordError == null) {
      final response = await http.post(
        Uri.parse(
            'https://honeydew-panther-755692.hostingersite.com//register.php'),
        body: {
          'username': usernameController.text,
          'password': passwordController.text,
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == 'success') {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text("Registrasi berhasil"),
              backgroundColor: Colors.green,
            ),
          );
          // Arahkan ke HalamanLogin setelah registrasi berhasil
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(
              builder: (context) => HalamanLogin(widget.spInstance),
            ),
          );
        } else {
          setState(() {
            usernameError = data['message'];
          });
        }
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text("Terjadi kesalahan server"),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Registrasi"),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
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
                  onChanged: (value) => validasiUsername(),
                  decoration: InputDecoration(
                    icon: const Icon(Icons.person),
                    label: const Text("Username"),
                    errorText: usernameError,
                    border: const OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: passwordController,
                  onChanged: (value) {
                    validasiPassword();
                    validasiKonfirmasiPassword();
                  },
                  obscureText: !showPassword,
                  decoration: InputDecoration(
                    icon: const Icon(Icons.key),
                    label: const Text("Password"),
                    errorText: passwordError,
                    suffixIcon: GestureDetector(
                      onTap: toggleShowPassword,
                      child: Icon(showPassword
                          ? Icons.visibility
                          : Icons.visibility_off),
                    ),
                    border: const OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: konfirmasiPasswordController,
                  onChanged: (value) => validasiKonfirmasiPassword(),
                  obscureText: !showKonfirmasiPassword,
                  decoration: InputDecoration(
                    icon: const Icon(Icons.check_circle),
                    label: const Text("Konfirmasi Password"),
                    errorText: konfirmasiPasswordError,
                    suffixIcon: GestureDetector(
                      onTap: toggleShowKonfirmasiPassword,
                      child: Icon(showKonfirmasiPassword
                          ? Icons.visibility
                          : Icons.visibility_off),
                    ),
                    border: const OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 24),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: _register,
                        icon: const Icon(Icons.person_add),
                        label: const Text("Registrasi"),
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
                      // Arahkan ke HalamanLogin
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => HalamanLogin(widget.spInstance),
                        ),
                      );
                    },
                    child: const Text(
                      "Login",
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
