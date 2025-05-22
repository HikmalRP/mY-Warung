import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'nota_service.dart';

class HalamanEditProfile extends StatefulWidget {
  final String currentUsername;

  const HalamanEditProfile(this.currentUsername, {Key? key}) : super(key: key);

  @override
  State<StatefulWidget> createState() => _HalamanEditProfileState();
}

class _HalamanEditProfileState extends State<HalamanEditProfile> {
  final TextEditingController usernameController = TextEditingController();
  final TextEditingController passwordController = TextEditingController();
  final TextEditingController konfirmasiPasswordController =
      TextEditingController();

  bool showPassword = false;
  bool showKonfirmasiPassword = false;

  String? usernameError;
  String? passwordError;
  String? konfirmasiPasswordError;

  @override
  void initState() {
    super.initState();
    usernameController.text = widget.currentUsername;
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

  Future<void> simpanPerubahan() async {
    validasiUsername();
    validasiPassword();
    validasiKonfirmasiPassword();

    if (usernameError == null &&
        passwordError == null &&
        konfirmasiPasswordError == null) {
      final response = await http.post(
        Uri.parse(
            'https://honeydew-panther-755692.hostingersite.com//update_user_profile.php'),
        body: {
          'current_username': widget.currentUsername,
          'new_username': usernameController.text,
          'password': passwordController.text,
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == 'success') {
          // Simpan username baru ke SharedPreferences
          await SharedPreferences.getInstance().then((prefs) {
            prefs.setString('currentUsername', usernameController.text);
          });

          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text("Profil berhasil diperbarui"),
              backgroundColor: Colors.green,
            ),
          );
          Navigator.pop(context);
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

  Future<List<Map<String, dynamic>>> _fetchPurchaseHistory() async {
    final response = await http.get(Uri.parse(
        'https://honeydew-panther-755692.hostingersite.com//get_purchase_history.php?username=${widget.currentUsername}'));
    if (response.statusCode == 200) {
      return List<Map<String, dynamic>>.from(jsonDecode(response.body));
    } else {
      throw Exception('Failed to load purchase history');
    }
  }

  Future<void> _generateNota(Map<String, dynamic> data) async {
    final notaService = NotaService();
    try {
      await notaService.generateNota(
        username: widget.currentUsername,
        totalHargaDenganOngkir: data['total_with_shipping'],
        ongkosKirim: data['shipping_cost'],
        kotaAsalNama: data['origin'],
        kotaTujuanNama: data['destination'],
        courier: data['courier'],
        selectedItems:
            List<Map<String, dynamic>>.from(jsonDecode(data['items'])),
        jumlahPembayaran: data['amount_paid'],
        kembalian: data['change_amount'],
      );
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text("Nota berhasil dibuat!"),
          backgroundColor: Colors.green,
        ),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text("Gagal membuat nota: $e"),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Edit Profile"),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Center(
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // Form untuk update profil
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
                    icon: const Icon(Icons.lock),
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
                  width: double.infinity, // Membuat tombol selebar parent
                  child: ElevatedButton.icon(
                    onPressed: simpanPerubahan,
                    icon: const Icon(Icons.save),
                    label: const Text("Simpan"),
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                // Bagian untuk menampilkan "History Pembelian"
                const Text("History Pembelian",
                    style:
                        TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                FutureBuilder<List<Map<String, dynamic>>>(
                  future: _fetchPurchaseHistory(),
                  builder: (context, snapshot) {
                    if (snapshot.connectionState == ConnectionState.waiting) {
                      return const CircularProgressIndicator();
                    } else if (snapshot.hasError) {
                      return Text("Error: ${snapshot.error}");
                    } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
                      return const Text("Belum ada history pembelian.");
                    }
                    final purchaseHistory = snapshot.data!;
                    return ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: purchaseHistory.length,
                      itemBuilder: (context, index) {
                        final item = purchaseHistory[index];
                        return Card(
                          margin: const EdgeInsets.symmetric(vertical: 8.0),
                          child: ListTile(
                            title: Text("Transaksi ${item['id']}"),
                            subtitle: Text(
                                "Total: Rp ${item['total_with_shipping']}\nTanggal: ${item['created_at']}"),
                            trailing: const Icon(Icons.arrow_forward),
                            onTap: () {
                              _generateNota(
                                  item); // Membuat PDF nota untuk item ini
                            },
                          ),
                        );
                      },
                    );
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
