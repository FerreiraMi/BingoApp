import '../models/sponsor.dart';

class SponsorService {
  final List<Sponsor> _sponsors = [];

  List<Sponsor> get sponsors => _sponsors;

  void addSponsor(Sponsor sponsor) {
    if (_sponsors.any((s) => s.number == sponsor.number)) {
      throw Exception("Número já escolhido");
    }
    _sponsors.add(sponsor);
  }

  Sponsor? getByNumber(int number) {
    try {
      return _sponsors.firstWhere((s) => s.number == number);
    } catch (_) {
      return null;
    }
  }
}