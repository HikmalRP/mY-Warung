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
  TextEditingController namaController = TextEditingController();
  TextEditingController usernameController = TextEditingController();
  TextEditingController passwordController = TextEditingController();
  TextEditingController konfirmasiPasswordController = TextEditingController();
  bool showPassword = false, showKonfirmasiPassword = false;
  String? namaError, usernameError, passwordError, konfirmasiPasswordError;

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

  void validasiNama() {
    setState(() {
      namaError =
          namaController.text.isEmpty ? "Nama tidak boleh kosong" : null;
    });
  }

  void validasiUsername() {
    setState(() {
      usernameError = usernameController.text.isEmpty
          ? "Username tidak boleh kosong"
          : null;
    });
  }

  void validasiPassword() {
    String text = passwordController.text;
    if (text.isEmpty) {
      passwordError = "Password tidak boleh kosong";
    } else if (text.length < 8) {
      passwordError = "Password harus minimal 8 karakter";
    } else if (!RegExp(r"[A-Z]").hasMatch(text)) {
      passwordError = "Password harus mengandung huruf besar";
    } else if (!RegExp(r"[a-z]").hasMatch(text)) {
      passwordError = "Password harus mengandung huruf kecil";
    } else if (!RegExp(r"[0-9]").hasMatch(text)) {
      passwordError = "Password harus mengandung angka";
    } else {
      passwordError = null;
    }
    setState(() {});
  }

  void validasiKonfirmasiPassword() {
    setState(() {
      konfirmasiPasswordError =
          konfirmasiPasswordController.text != passwordController.text
              ? "Password tidak sama"
              : null;
    });
  }

  Future<void> _register() async {
    validasiNama();
    validasiUsername();
    validasiPassword();
    validasiKonfirmasiPassword();
    if (namaError == null &&
        usernameError == null &&
        passwordError == null &&
        konfirmasiPasswordError == null) {
      final response = await http.post(
        Uri.parse('http://10.0.2.2/mY_Warung/Web/pembeli/register.php'),
        body: {
          'nama': namaController.text,
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
                backgroundColor: Colors.green),
          );
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
              backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Registrasi")),
      resizeToAvoidBottomInset: true,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Center(
            child: Container(
              constraints: const BoxConstraints(maxWidth: 500),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: 24),
                  TextField(
                    controller: namaController,
                    onChanged: (value) => validasiNama(),
                    decoration: InputDecoration(
                      icon: const Icon(Icons.badge),
                      label: const Text("Nama"),
                      errorText: namaError,
                      border: const OutlineInputBorder(),
                    ),
                  ),
                  const SizedBox(height: 16),
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
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: _register,
                      icon: const Icon(Icons.person_add),
                      label: const Text("Registrasi"),
                      style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 16.0),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Center(
                    child: TextButton(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) =>
                                HalamanLogin(widget.spInstance),
                          ),
                        );
                      },
                      child:
                          const Text("Login", style: TextStyle(fontSize: 16)),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
