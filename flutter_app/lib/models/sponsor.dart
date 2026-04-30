class Sponsor {
  final String name;
  final String image;
  final int number;
  bool shown;

  Sponsor({
    required this.name,
    required this.image,
    required this.number,
    this.shown = false,
  });
}