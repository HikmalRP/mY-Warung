import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:image_picker/image_picker.dart';
import 'package:http/http.dart' as http;
import 'package:uas_ppb/nota_service.dart'; // Ganti dengan path yang sesuai untuk file Anda

class FormPembayaran extends StatefulWidget {
  final String username;
  final String kotaAsalNama;
  final String kotaTujuanNama;
  final int weight;
  final String courier;
  final Map<String, dynamic> selectedLayanan;
  final int totalHarga;
  final List<Map<String, dynamic>> selectedItems;

  const FormPembayaran({
    required this.username,
    required this.kotaAsalNama,
    required this.kotaTujuanNama,
    required this.weight,
    required this.courier,
    required this.selectedLayanan,
    required this.totalHarga,
    required this.selectedItems,
    Key? key,
  }) : super(key: key);

  @override
  _FormPembayaranState createState() => _FormPembayaranState();
}

class _FormPembayaranState extends State<FormPembayaran> {
  final ImagePicker _picker = ImagePicker();
  XFile? _selectedImage;
  int jumlahPembayaran = 0;
  int kembalian = 0;

  @override
  Widget build(BuildContext context) {
    final int ongkosKirim = widget.selectedLayanan['harga'] ?? 0;
    final int totalHargaDenganOngkir = widget.totalHarga + ongkosKirim;

    return Scaffold(
      appBar: AppBar(title: const Text('Form Pembayaran')),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text("Detail Transaksi",
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              _buildDetailRow("Username:", widget.username),
              _buildDetailRow("Kota Asal:", widget.kotaAsalNama),
              _buildDetailRow("Kota Tujuan:", widget.kotaTujuanNama),
              _buildDetailRow("Berat Paket:", "${widget.weight} gram"),
              _buildDetailRow("Kurir:", widget.courier),
              _buildDetailRow("Layanan:", widget.selectedLayanan['service']),
              _buildDetailRow("Ongkos Kirim:", "Rp $ongkosKirim"),
              _buildDetailRow("Total Transaksi:", "Rp ${widget.totalHarga}"),
              _buildDetailRow(
                  "Total dengan Ongkir:", "Rp $totalHargaDenganOngkir"),
              const Divider(),
              const Text("Barang yang Dibeli",
                  style: TextStyle(fontWeight: FontWeight.bold)),
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: widget.selectedItems.length,
                itemBuilder: (context, index) {
                  final item = widget.selectedItems[index];
                  return ListTile(
                    title: Text(item['nama']),
                    subtitle: Text("Jumlah: ${item['jumlah']}"),
                    trailing: Text("Rp ${item['harga'] * item['jumlah']}"),
                  );
                },
              ),
              const Divider(),
              const SizedBox(height: 16),
              TextField(
                keyboardType: TextInputType.number,
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                decoration: const InputDecoration(
                  labelText: "Jumlah Pembayaran",
                  border: OutlineInputBorder(),
                ),
                onChanged: (value) {
                  setState(() {
                    jumlahPembayaran = int.tryParse(value) ?? 0;
                    kembalian = jumlahPembayaran - totalHargaDenganOngkir;
                  });
                },
              ),
              const SizedBox(height: 16),
              Text("Kembalian: Rp ${kembalian < 0 ? 0 : kembalian}"),
              const SizedBox(height: 16),
              _selectedImage != null
                  ? Image.file(
                      File(_selectedImage!.path),
                      width: 200,
                      height: 200,
                      fit: BoxFit.cover,
                    )
                  : const Text("Belum ada gambar"),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: () async {
                  final XFile? pickedFile = await _picker.pickImage(
                    source: ImageSource.gallery,
                  );
                  if (pickedFile != null) {
                    setState(() {
                      _selectedImage = pickedFile;
                    });
                  }
                },
                icon: const Icon(Icons.photo),
                label: const Text("Pilih dari Galeri"),
              ),
              ElevatedButton.icon(
                onPressed: () async {
                  final XFile? pickedFile = await _picker.pickImage(
                    source: ImageSource.camera,
                  );
                  if (pickedFile != null) {
                    setState(() {
                      _selectedImage = pickedFile;
                    });
                  }
                },
                icon: const Icon(Icons.camera),
                label: const Text("Ambil Foto"),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: (_selectedImage != null &&
                        jumlahPembayaran >= totalHargaDenganOngkir &&
                        jumlahPembayaran > 0)
                    ? () async {
                        final notaService = NotaService();
                        try {
                          // Panggil fungsi generateNota untuk membuat dan menyimpan nota PDF
                          final filePath = await notaService.generateNota(
                            username: widget.username,
                            totalHargaDenganOngkir: totalHargaDenganOngkir,
                            ongkosKirim: ongkosKirim,
                            kotaAsalNama: widget.kotaAsalNama,
                            kotaTujuanNama: widget.kotaTujuanNama,
                            courier: widget.courier,
                            selectedItems: widget.selectedItems,
                            jumlahPembayaran: jumlahPembayaran,
                            kembalian: kembalian,
                            imagePath: _selectedImage!.path,
                          );

                          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                            content:
                                Text('Nota berhasil disimpan di $filePath'),
                            backgroundColor: Colors.green,
                          ));
                        } catch (e) {
                          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                            content: Text('Gagal membuat nota: $e'),
                            backgroundColor: Colors.red,
                          ));
                        }

                        // Simpan transaksi ke database
                        await _saveTransaction(
                          origin: widget.kotaAsalNama,
                          destination: widget.kotaTujuanNama,
                          weight: widget.weight,
                          courier: widget.courier,
                          service: widget.selectedLayanan['service'],
                          shippingCost: ongkosKirim,
                          totalWithShipping: totalHargaDenganOngkir,
                          amountPaid: jumlahPembayaran,
                          change: kembalian,
                          items: widget.selectedItems,
                          paymentProofPath: _selectedImage!.path,
                        );

                        Navigator.pop(context);
                      }
                    : null,
                child: const Text("Bayar"),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontWeight: FontWeight.bold)),
          Text(value),
        ],
      ),
    );
  }

  Future<void> _saveTransaction({
    required String origin,
    required String destination,
    required int weight,
    required String courier,
    required String service,
    required int shippingCost,
    required int totalWithShipping,
    required int amountPaid,
    required int change,
    required List<Map<String, dynamic>> items,
    required String paymentProofPath,
  }) async {
    try {
      final uri = Uri.parse(
          "https://honeydew-panther-755692.hostingersite.com//save_transaction.php");
      final request = http.MultipartRequest('POST', uri);

      request.fields['username'] = widget.username;
      request.fields['origin'] = origin;
      request.fields['destination'] = destination;
      request.fields['weight'] = weight.toString();
      request.fields['courier'] = courier;
      request.fields['service'] = service;
      request.fields['shipping_cost'] = shippingCost.toString();
      request.fields['total_with_shipping'] = totalWithShipping.toString();
      request.fields['amount_paid'] = amountPaid.toString();
      request.fields['change'] = change.toString();
      request.fields['items'] = jsonEncode(items);

      request.files.add(await http.MultipartFile.fromPath(
        'payment_proof',
        paymentProofPath,
      ));

      final response = await request.send();

      if (response.statusCode == 200) {
        print("Transaksi berhasil disimpan.");
      } else {
        print("Gagal menyimpan transaksi.");
      }
    } catch (e) {
      print("Error saat menyimpan transaksi: $e");
    }
  }
}
