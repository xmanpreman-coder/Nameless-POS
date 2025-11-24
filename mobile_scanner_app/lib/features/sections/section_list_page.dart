import 'package:flutter/material.dart';

class SectionListPage extends StatelessWidget {
  const SectionListPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Sections'),
      ),
      body: const Center(
        child: Text('Sections List UI will be here.'),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // TODO: Implement create new section
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}
