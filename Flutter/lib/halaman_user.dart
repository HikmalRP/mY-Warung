import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:uas_ppb/halaman_login.dart';
import 'package:uas_ppb/halaman_register.dart';

class HalamanUser extends StatelessWidget {
  final SharedPreferences spInstance;
  const HalamanUser(this.spInstance, {super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Container(
          constraints: const BoxConstraints(maxWidth: 500),
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Image.asset(
                  'assets/logo.png',
                  width: 200,
                  height: 200,
                ),
                const SizedBox(height: 30),
                Text(
                  "Selamat Datang",
                  style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                        fontSize: 28,
                        fontWeight: FontWeight.bold,
                      ),
                  textAlign: TextAlign.center,
                ),
                Text(
                  "Silahkan",
                  style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                        fontSize: 28,
                        fontWeight: FontWeight.bold,
                      ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 50),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    label: const Text(
                      "Login",
                      style: TextStyle(fontSize: 20),
                    ),
                    icon: const Icon(Icons.login, size: 24),
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => HalamanLogin(spInstance),
                        ),
                      );
                    },
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  child: Text(
                    "Atau",
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          fontSize: 18,
                        ),
                  ),
                ),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    label: const Text(
                      "Registrasi",
                      style: TextStyle(fontSize: 20),
                    ),
                    icon: const Icon(Icons.person, size: 24),
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => HalamanRegistrasi(spInstance),
                        ),
                      );
                    },
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
