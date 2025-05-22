import 'dart:convert';
import 'package:dropdown_search/dropdown_search.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:uas_ppb/model/model_kota.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:http/http.dart' as http;
import 'package:uas_ppb/halaman_item.dart';
import 'package:uas_ppb/halaman_edit_profile.dart';
import 'package:uas_ppb/halaman_login.dart';
import 'package:uas_ppb/api_helper.dart';
import 'package:uas_ppb/form_pembayaran.dart';

class HalamanDashboard extends StatefulWidget {
  final SharedPreferences spInstance;
  final String currentUsername;

  const HalamanDashboard(this.spInstance, this.currentUsername, {Key? key})
      : super(key: key);

  @override
  _HalamanDashboardState createState() => _HalamanDashboardState();
}

class _HalamanDashboardState extends State<HalamanDashboard> {
  Map<String, dynamic>?
      selectedLayanan; // Inisialisasi variabel untuk layanan yang dipilih
  List<Map<String, dynamic>> selectedItems = [];
  int totalJual = 0;
  int totalHarga = 0;
  int jumlahPembayaran = 0;
  int kembalian = 0;
  late String updatedUsername;
  late Future<List<Map<String, dynamic>>> products;
  String? kotaAsal, kotaTujuan, kurir;
  String berat = '';
  String? origin;
  String? destination;
  int? weight;
  String? courier;
  String? kotaAsalNama; // Nama kota asal
  String? kotaTujuanNama; // Nama kota tujuan

  @override
  void initState() {
    super.initState();
    updatedUsername = widget.currentUsername;
    _loadProducts();
  }

  Future<void> _loadProducts() async {
    products = APIHelper().getAllProducts();
  }

  void _resetTransaction() {
    setState(() {
      totalJual = 0;
      totalHarga = 0;
      jumlahPembayaran = 0;
      kembalian = 0;
      selectedItems.clear(); // Kosongkan daftar barang yang dipilih
    });
  }

  Future<void> _refreshDashboard() async {
    final updatedUsernameSp = widget.spInstance.getString('currentUsername');
    setState(() {
      updatedUsername = updatedUsernameSp ?? widget.currentUsername;
      _loadProducts(); // Memuat ulang produk
    });
  }

  void _tambahTotalJual(Map<String, dynamic> item) {
    setState(() {
      // Cari apakah barang sudah ada di selectedItems
      final existingItemIndex = selectedItems.indexWhere(
        (selectedItem) => selectedItem['nama'] == item['nama'],
      );

      if (existingItemIndex != -1) {
        // Jika sudah ada, tambahkan jumlahnya
        selectedItems[existingItemIndex]['jumlah'] += 1;
      } else {
        // Jika belum ada, tambahkan sebagai barang baru
        selectedItems.add({
          'nama': item['nama'],
          'harga': int.tryParse(item['harga'].toString()) ?? 0,
          'jumlah': 1,
          'linkGambar': item['linkGambar'],
        });
      }

      // Perbarui totalJual dan totalHarga
      totalJual += 1; // Tambahkan jumlah barang
      totalHarga += int.tryParse(item['harga'].toString()) ?? 0;
    });
  }

  Future<List<Map<String, dynamic>>> getLayananOngkir({
    required String origin,
    required String destination,
    required int weight,
    required String courier,
  }) async {
    const String apiKey = 'd84d2e7a7fde64bc36b2b313b113de0a';
    const String baseUrl = 'https://api.rajaongkir.com/starter/cost';

    try {
      final response = await http.post(
        Uri.parse(baseUrl),
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'key': apiKey,
        },
        body: {
          'origin': origin,
          'destination': destination,
          'weight': weight.toString(),
          'courier': courier,
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final List<dynamic> services =
            data['rajaongkir']['results'][0]['costs'];

        if (services.isEmpty) {
          throw Exception('Tidak ada layanan tersedia.');
        }

        return services.map<Map<String, dynamic>>((service) {
          return {
            'service': service['service'], // Nama layanan
            'harga': service['cost'][0]['value'], // Harga layanan
          };
        }).toList();
      } else {
        throw Exception('Gagal memuat layanan ongkir: ${response.statusCode}');
      }
    } catch (e) {
      print('Error: $e');
      return [];
    }
  }

  void _navigateToPaymentForm() {
    if (origin == null ||
        destination == null ||
        weight == null ||
        courier == null ||
        selectedLayanan == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
            content: Text('Data ongkir tidak lengkap. Isi ulang form.')),
      );
      return;
    }

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => FormPembayaran(
          username: updatedUsername,
          kotaAsalNama: kotaAsalNama!, // Nama kota asal
          kotaTujuanNama: kotaTujuanNama!, // Nama kota tujuan
          weight: weight!,
          courier: courier!,
          selectedLayanan: selectedLayanan!,
          totalHarga: totalHarga,
          selectedItems: selectedItems,
        ),
      ),
    ).then((_) {
      _resetTransaction(); // Reset transaction after returning from payment form
    });
  }

// Fungsi bantu untuk membuat baris detail
  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label),
          Text(
            value,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }

  Future<void> _showFormOngkir() async {
    await showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              title: const Text("Form Cek Ongkir"),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // Kota Asal Dropdown
                    DropdownSearch<ModelKota>(
                      dropdownDecoratorProps: const DropDownDecoratorProps(
                        dropdownSearchDecoration: InputDecoration(
                          labelText: "Kota Asal",
                          hintText: "Pilih Kota Asal",
                        ),
                      ),
                      popupProps: const PopupProps.menu(showSearchBox: true),
                      onChanged: (value) {
                        setState(() {
                          kotaAsal = value?.cityId;
                          kotaAsalNama = "${value?.type} ${value?.cityName}";
                          origin = kotaAsal; // Assign to origin
                        });
                      },
                      itemAsString: (item) => "${item.type} ${item.cityName}",
                      asyncItems: (text) async {
                        try {
                          var response = await http.get(Uri.parse(
                              "https://api.rajaongkir.com/starter/city?key=d84d2e7a7fde64bc36b2b313b113de0a")); // Replace with your API key
                          if (response.statusCode == 200) {
                            List allKota = (jsonDecode(response.body)
                                    as Map<String, dynamic>)['rajaongkir']
                                ['results'];
                            return ModelKota.fromJsonList(allKota);
                          } else {
                            throw Exception("Gagal memuat data kota.");
                          }
                        } catch (e) {
                          return [];
                        }
                      },
                    ),
                    const SizedBox(height: 20),
                    // Kota Tujuan Dropdown
                    DropdownSearch<ModelKota>(
                      dropdownDecoratorProps: const DropDownDecoratorProps(
                        dropdownSearchDecoration: InputDecoration(
                          labelText: "Kota Tujuan",
                          hintText: "Pilih Kota Tujuan",
                        ),
                      ),
                      popupProps: const PopupProps.menu(showSearchBox: true),
                      onChanged: (value) {
                        setState(() {
                          kotaTujuan = value?.cityId;
                          kotaTujuanNama = "${value?.type} ${value?.cityName}";
                          destination = kotaTujuan; // Assign to destination
                        });
                      },
                      itemAsString: (item) => "${item.type} ${item.cityName}",
                      asyncItems: (text) async {
                        try {
                          var response = await http.get(Uri.parse(
                              "https://api.rajaongkir.com/starter/city?key=d84d2e7a7fde64bc36b2b313b113de0a")); // Replace with your API key
                          if (response.statusCode == 200) {
                            List allKota = (jsonDecode(response.body)
                                    as Map<String, dynamic>)['rajaongkir']
                                ['results'];
                            return ModelKota.fromJsonList(allKota);
                          } else {
                            throw Exception("Gagal memuat data kota.");
                          }
                        } catch (e) {
                          return [];
                        }
                      },
                    ),
                    const SizedBox(height: 20),
                    // Berat Paket Input
                    TextField(
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: "Berat Paket (gram)",
                        hintText: "Masukkan Berat Paket",
                      ),
                      onChanged: (value) {
                        setState(() {
                          berat = value;
                          weight = int.tryParse(berat);
                        });
                      },
                    ),
                    const SizedBox(height: 20),
                    // Kurir Dropdown
                    DropdownSearch<String>(
                      dropdownDecoratorProps: const DropDownDecoratorProps(
                        dropdownSearchDecoration: InputDecoration(
                          labelText: "Kurir",
                          hintText: "Pilih Kurir",
                        ),
                      ),
                      popupProps: const PopupProps.menu(),
                      items: const ["JNE", "TIKI", "POS"],
                      onChanged: (value) {
                        setState(() {
                          kurir = value?.toLowerCase();
                          courier = kurir;
                        });
                      },
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () {
                    setState(() {
                      _resetTransaction(); // Reset all variables
                    });
                    Navigator.pop(context);
                  },
                  style: TextButton.styleFrom(foregroundColor: Colors.red),
                  child: const Text("Batal"),
                ),
                TextButton(
                  onPressed: () {
                    Navigator.pop(context);
                  },
                  child: const Text("Tutup"),
                ),
                ElevatedButton(
                  onPressed: (origin != null &&
                          destination != null &&
                          weight != null &&
                          courier != null)
                      ? () {
                          Navigator.pop(context);
                          _showFormLayanan(); // Proceed to service form
                        }
                      : null,
                  child: const Text("Lanjut"),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _showFormLayanan() async {
    List<Map<String, dynamic>> layananOngkir = [];

    try {
      layananOngkir = await getLayananOngkir(
        origin: origin!,
        destination: destination!,
        weight: weight!,
        courier: courier!,
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal memuat layanan ongkir: $e')),
      );
      return;
    }

    await showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              title: const Text("Pilih Layanan Ongkir"),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: layananOngkir.map((layanan) {
                  return RadioListTile<Map<String, dynamic>>(
                    title: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(layanan['service']),
                        Text(
                          "Rp ${layanan['harga']}",
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Colors.green,
                          ),
                        ),
                      ],
                    ),
                    value: layanan,
                    groupValue: selectedLayanan,
                    onChanged: (value) {
                      setState(() {
                        selectedLayanan = value;
                      });
                    },
                  );
                }).toList(),
              ),
              actions: [
                TextButton(
                  onPressed: () {
                    setState(() {
                      _resetTransaction();
                    });
                    Navigator.pop(context);
                  },
                  style: TextButton.styleFrom(foregroundColor: Colors.red),
                  child: const Text("Batal"),
                ),
                TextButton(
                  onPressed: () {
                    Navigator.pop(context);
                    _showFormOngkir();
                  },
                  child: const Text("Kembali"),
                ),
                ElevatedButton(
                  onPressed: selectedLayanan != null
                      ? () {
                          Navigator.pop(context);
                          _navigateToPaymentForm(); // Navigate to payment form
                        }
                      : null,
                  child: const Text("Lanjut"),
                ),
              ],
            );
          },
        );
      },
    );
  }

  Future<void> _launchCaller() async {
    final Uri uri = Uri(scheme: "tel", path: "+6282223509038");
    if (!await launchUrl(uri)) {
      throw Exception("Gagal membuka link!");
    }
  }

  Future<void> _launchSMS() async {
    final Uri uri = Uri(scheme: "sms", path: "+6282223509038");
    if (!await launchUrl(uri)) {
      throw Exception("Gagal membuka link!");
    }
  }

  Future<void> _launchMap() async {
    const mapUrl = "https://maps.app.goo.gl/GSAz8wbbS8NK5ALE9";
    if (await canLaunch(mapUrl)) {
      await launch(mapUrl);
    } else {
      throw "Gagal membuka link! $mapUrl";
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          "Dashboard",
          style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold),
        ),
        actions: [
          Row(
            children: [
              Text(updatedUsername),
              const SizedBox(width: 8),
              const Icon(Icons.person),
              PopupMenuButton<String>(
                onSelected: (value) {
                  switch (value) {
                    case 'Call Center':
                      _launchCaller();
                      break;
                    case 'SMS Center':
                      _launchSMS();
                      break;
                    case 'Lokasi/Maps':
                      _launchMap();
                      break;
                    case 'Update User & Password':
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => HalamanEditProfile(
                            updatedUsername,
                          ),
                        ),
                      ).then((_) {
                        // Perbarui dashboard setelah kembali
                        _refreshDashboard();
                      });
                      break;
                    case 'Refresh Dashboard':
                      _refreshDashboard();
                      break;
                    case 'Log Out':
                      Navigator.pushReplacement(
                        context,
                        MaterialPageRoute(
                          builder: (context) => HalamanLogin(widget.spInstance),
                        ),
                      );
                      break;
                  }
                },
                itemBuilder: (BuildContext context) => [
                  const PopupMenuItem(
                      value: 'Call Center', child: Text('Call Center')),
                  const PopupMenuItem(
                      value: 'SMS Center', child: Text('SMS Center')),
                  const PopupMenuItem(
                      value: 'Lokasi/Maps', child: Text('Lokasi/Maps')),
                  const PopupMenuItem(
                      value: 'Update User & Password',
                      child: Text('Update User & Password')),
                  const PopupMenuDivider(),
                  const PopupMenuItem(
                      value: 'Refresh Dashboard',
                      child: Text('Refresh Dashboard')),
                  const PopupMenuItem(value: 'Log Out', child: Text('Log Out')),
                ],
              ),
            ],
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _refreshDashboard,
        child: FutureBuilder<List<Map<String, dynamic>>>(
          future: products,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snapshot.hasError) {
              return const Center(child: Text('Gagal memuat data'));
            }
            final items = snapshot.data ?? [];
            return items.isEmpty
                ? const Center(child: Text('Belum ada produk yang tersedia'))
                : Padding(
                    padding: const EdgeInsets.all(16),
                    child: GridView.builder(
                      gridDelegate:
                          const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 2,
                        mainAxisSpacing: 7,
                        crossAxisSpacing: 7,
                        childAspectRatio: 0.8,
                      ),
                      itemCount: items.length,
                      itemBuilder: (context, index) {
                        final item = items[index];
                        return GestureDetector(
                          onTap: () {
                            _tambahTotalJual(item); // Kirim seluruh objek item
                          },
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              ClipRRect(
                                borderRadius: BorderRadius.circular(8),
                                child: Image.network(
                                  item['linkGambar'],
                                  fit: BoxFit.cover,
                                  width: double.infinity,
                                  height: 100,
                                  errorBuilder: (_, __, ___) => Container(
                                    color: Colors.grey[300],
                                    alignment: Alignment.center,
                                    child: const Text('No Image'),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 8),
                              GestureDetector(
                                onTap: () {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (context) => HalamanItem(item),
                                    ),
                                  );
                                },
                                child: Text(
                                  item['nama'],
                                  style:
                                      Theme.of(context).textTheme.titleMedium,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                item['deskripsi'],
                                style: Theme.of(context).textTheme.bodyMedium,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 4),
                              Text(
                                "Rp ${item['harga']}",
                                style: Theme.of(context)
                                    .textTheme
                                    .bodyLarge
                                    ?.copyWith(
                                      color: Colors.green,
                                      fontWeight: FontWeight.bold,
                                    ),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                  );
          },
        ),
      ),
      bottomNavigationBar: InkWell(
        onTap: _showFormOngkir,
        child: Container(
          padding: const EdgeInsets.all(16),
          color: Colors.grey[200],
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                "Total Jual: $totalJual", // Membaca nilai dari totalJual
                style: Theme.of(context)
                    .textTheme
                    .titleLarge
                    ?.copyWith(fontWeight: FontWeight.bold),
              ),
              Expanded(
                child: Text(
                  "Total Harga: Rp $totalHarga", // Membaca nilai dari totalHarga
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold, color: Colors.green),
                  textAlign: TextAlign.right,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
