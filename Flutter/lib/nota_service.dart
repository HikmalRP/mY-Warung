import 'dart:io';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:file_picker/file_picker.dart';

class NotaService {
  Future<String?> generateNota({
    required String username, // Tambahkan username sebagai parameter
    required int totalHargaDenganOngkir,
    required int ongkosKirim,
    required String kotaAsalNama,
    required String kotaTujuanNama,
    required String courier,
    required List<Map<String, dynamic>> selectedItems,
    required int jumlahPembayaran,
    required int kembalian,
    String? imagePath,
  }) async {
    final pdf = pw.Document();

    // Membuat halaman PDF
    pdf.addPage(
      pw.Page(
        pageFormat: PdfPageFormat.a4,
        margin: const pw.EdgeInsets.all(32),
        build: (pw.Context context) {
          return pw.Column(
            crossAxisAlignment: pw.CrossAxisAlignment.start,
            children: [
              // Header: Nama aplikasi
              pw.Text(
                "Warung Ajib",
                style: pw.TextStyle(
                  fontSize: 24,
                  fontWeight: pw.FontWeight.bold,
                  color: PdfColors.yellow,
                ),
              ),
              pw.Divider(thickness: 2, color: PdfColors.yellow),
              pw.SizedBox(height: 10),

              // Detail transaksi
              pw.Text("Detail Transaksi",
                  style: pw.TextStyle(
                      fontSize: 18, fontWeight: pw.FontWeight.bold)),
              pw.SizedBox(height: 10),

              // Tambahkan Username di bawah "Detail Transaksi" dan sebelum "Kota Asal"
              _buildDetailRow("Username:", username),

              _buildDetailRow("Kota Asal:", kotaAsalNama),
              _buildDetailRow("Kota Tujuan:", kotaTujuanNama),
              _buildDetailRow("Kurir:", courier.toUpperCase()),
              _buildDetailRow("Ongkos Kirim:", "Rp $ongkosKirim"),
              _buildDetailRow("Total Transaksi:", "Rp $totalHargaDenganOngkir"),
              _buildDetailRow("Jumlah Dibayarkan:", "Rp $jumlahPembayaran"),
              _buildDetailRow("Kembalian:", "Rp $kembalian"),
              pw.SizedBox(height: 20),

              // Daftar barang yang dibeli
              pw.Text("Barang yang Dibeli",
                  style: pw.TextStyle(
                      fontSize: 18, fontWeight: pw.FontWeight.bold)),
              pw.SizedBox(height: 10),
              pw.Table(
                border: pw.TableBorder.all(),
                children: [
                  pw.TableRow(
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(8.0),
                        child: pw.Text("Nama Barang",
                            style:
                                pw.TextStyle(fontWeight: pw.FontWeight.bold)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(8.0),
                        child: pw.Text("Jumlah",
                            style:
                                pw.TextStyle(fontWeight: pw.FontWeight.bold)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(8.0),
                        child: pw.Text("Harga",
                            style:
                                pw.TextStyle(fontWeight: pw.FontWeight.bold)),
                      ),
                    ],
                  ),
                  ...selectedItems.map((item) {
                    return pw.TableRow(
                      children: [
                        pw.Padding(
                          padding: const pw.EdgeInsets.all(8.0),
                          child: pw.Text(item['nama']),
                        ),
                        pw.Padding(
                          padding: const pw.EdgeInsets.all(8.0),
                          child: pw.Text("${item['jumlah']}"),
                        ),
                        pw.Padding(
                          padding: const pw.EdgeInsets.all(8.0),
                          child:
                              pw.Text("Rp ${item['harga'] * item['jumlah']}"),
                        ),
                      ],
                    );
                  }).toList(),
                ],
              ),
              pw.SizedBox(height: 20),

              // Bukti pembayaran (gambar)
              if (imagePath != null) ...[
                pw.Text("Bukti Pembayaran",
                    style: pw.TextStyle(
                        fontSize: 18, fontWeight: pw.FontWeight.bold)),
                pw.SizedBox(height: 10),
                pw.Center(
                  child: pw.Image(
                    pw.MemoryImage(File(imagePath).readAsBytesSync()),
                    width: 200,
                    height: 200,
                  ),
                ),
              ],
              pw.SizedBox(height: 20),

              // Footer
              pw.Divider(thickness: 1),
              pw.Center(
                child: pw.Text(
                  "Terima kasih telah berbelanja di Warung Ajib!",
                  style: pw.TextStyle(
                      fontSize: 14, fontStyle: pw.FontStyle.italic),
                ),
              ),
            ],
          );
        },
      ),
    );

    // Membuka dialog pemilih lokasi untuk menyimpan file
    String? selectedDirectory = await FilePicker.platform.getDirectoryPath();
    if (selectedDirectory == null) {
      // Jika pengguna tidak memilih lokasi, kembalikan null
      print("Penyimpanan dibatalkan.");
      return null;
    }

    // Membuat jalur file dengan nama file default
    final filePath = '$selectedDirectory/nota_pembayaran.pdf';

    // Menyimpan file PDF
    final file = File(filePath);
    await file.writeAsBytes(await pdf.save());

    print("Nota berhasil disimpan di $filePath");
    return filePath;
  }

  pw.Widget _buildDetailRow(String label, String value) {
    return pw.Row(
      mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
      children: [
        pw.Text(label, style: pw.TextStyle(fontWeight: pw.FontWeight.bold)),
        pw.Text(value),
      ],
    );
  }
}
