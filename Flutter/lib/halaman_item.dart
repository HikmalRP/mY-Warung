import 'package:flutter/material.dart';

class HalamanItem extends StatelessWidget {
  final Map<String, dynamic> item;

  const HalamanItem(this.item, {Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(item['nama'] ?? 'Item'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            item['linkGambar'] != null && item['linkGambar'].isNotEmpty
                ? Image.network(
                    item['linkGambar'],
                    fit: BoxFit.cover,
                    width: double.infinity,
                    height: 200, // Menambahkan tinggi gambar agar proporsional
                    errorBuilder: (_, __, ___) => Container(
                      color: Colors.grey[300],
                      alignment: Alignment.center,
                      child: const Text('No Image'),
                    ),
                  )
                : Container(
                    color: Colors.grey[300],
                    alignment: Alignment.center,
                    height: 200,
                    child: const Text('No Image'),
                  ),
            const SizedBox(height: 16),
            Text(
              item['nama'] ?? 'Nama tidak tersedia',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 8),
            Text(
              item['deskripsi'] ?? 'Deskripsi tidak tersedia',
              style: Theme.of(context).textTheme.bodyMedium,
            ),
            const SizedBox(height: 8),
            Text(
              "Harga: Rp ${item['harga'] ?? 0}",
              style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: Colors.green,
                  ),
            ),
          ],
        ),
      ),
    );
  }
}
