using System;
using System.IO;
using System.Text.RegularExpressions;

class Program
{
    static void Main()
    {
        string path = @"c:\laragon\www\AmigaTravel\flutter_app\lib\main.dart";
        string text = File.ReadAllText(path);
        
        string buildPattern = @"(?s)  Widget build\(BuildContext context\) \{\r?\n    return SingleChildScrollView\(.*?\r?\n    \);\r?\n  \}";
        
        string newBuild = @"  Widget build(BuildContext context) {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 1. Immersive Carousel
          const SizedBox(height: 8),
          SizedBox(
            height: 220,
            child: PageView.builder(
              controller: _promoPageController,
              onPageChanged: (i) => setState(() => _currentPromoPage = i),
              itemCount: 2 + _promotions.length,
              itemBuilder: (context, i) {
                if (i == 0) {
                  return const _WelcomeBanner();
                } else if (i == 1) {
                  return const _HeroVideoBanner();
                } else {
                  final promo = _promotions[i - 2];
                  final imgUrl = promo['image_url'] as String?;
                  return Container(
                    margin: const EdgeInsets.symmetric(horizontal: 16),
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(24),
                      color: kSlate100,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.1),
                          blurRadius: 10,
                          offset: const Offset(0, 5),
                        )
                      ]
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: imgUrl != null
                        ? Image.network(imgUrl,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => const Center(
                                child: Icon(Icons.broken_image, color: kSlate400, size: 40)))
                        : const Center(child: Icon(Icons.image, color: kSlate400, size: 40)),
                  );
                }
              },
            ),
          ),
          const SizedBox(height: 12),
          // Carousel Indicators
          if (_promotions.isNotEmpty || true)
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(
                2 + _promotions.length,
                (index) => AnimatedContainer(
                  duration: const Duration(milliseconds: 300),
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                  height: 6,
                  width: _currentPromoPage == index ? 24 : 6,
                  decoration: BoxDecoration(
                    color: _currentPromoPage == index ? kGreen : kSlate300,
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
              ),
            ),
          
          const SizedBox(height: 24),

          // 2. Track Booking (Modern glass/elevated style)
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.05),
                    blurRadius: 15,
                    offset: const Offset(0, 5),
                  )
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.location_on, color: kGreen, size: 20),
                      SizedBox(width: 8),
                      Text('Track Booking', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: kSlate800)),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Container(
                    decoration: BoxDecoration(
                      color: kSlate100,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: TextField(
                      decoration: InputDecoration(
                        hintText: 'Enter tracking number...',
                        hintStyle: const TextStyle(color: kSlate500, fontSize: 14),
                        border: InputBorder.none,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                        suffixIcon: Container(
                          margin: const EdgeInsets.all(4),
                          decoration: BoxDecoration(
                            color: kGreen,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.arrow_forward, color: Colors.white, size: 20),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 24),

          // 3. Quick Services Grid
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              children: [
                Expanded(
                  child: _ModernServiceCard(
                    title: 'Book Ferry',
                    subtitle: 'Starlite, 2GO',
                    icon: Icons.directions_boat,
                    color: kGreen,
                    onTap: widget.onBookFerry,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _ModernServiceCard(
                    title: 'Book Airline',
                    subtitle: 'PAL, CebuPac',
                    icon: Icons.flight_takeoff,
                    color: kPink,
                    onTap: widget.onBookAirline,
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // 4. Points & Vouchers
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              children: [
                Expanded(
                  child: GestureDetector(
                    onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GraciaPointsScreen())),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [kPink, Color(0xFFC2185B)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(color: kPink.withValues(alpha: 0.3), blurRadius: 10, offset: const Offset(0, 4))
                        ],
                      ),
                      child: const Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Icon(Icons.stars, color: Colors.white, size: 32),
                          SizedBox(height: 12),
                          Text('My Points', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
                          Text('View rewards', style: TextStyle(color: Colors.white70, fontSize: 12)),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: GestureDetector(
                    onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => VouchersScreen(onUseVoucher: () {
                          Navigator.pop(context);
                          widget.onBookFerry();
                        }))),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [kGreen, Color(0xFF1B5E20)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(color: kGreen.withValues(alpha: 0.3), blurRadius: 10, offset: const Offset(0, 4))
                        ],
                      ),
                      child: const Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Icon(Icons.local_activity, color: Colors.white, size: 32),
                          SizedBox(height: 12),
                          Text('Vouchers', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
                          Text('Claim promos', style: TextStyle(color: Colors.white70, fontSize: 12)),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // 5. Request Travel Booking (Modern Banner)
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: GestureDetector(
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const RequestBookingScreen())),
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: kSlate800,
                  borderRadius: BorderRadius.circular(20),
                  image: const DecorationImage(
                    image: AssetImage('assets/images/pattern.png'),
                    fit: BoxFit.cover,
                    opacity: 0.05,
                  ),
                  boxShadow: [
                    BoxShadow(color: kSlate800.withValues(alpha: 0.3), blurRadius: 10, offset: const Offset(0, 5))
                  ]
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.1),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.map, color: Colors.white, size: 28),
                    ),
                    const SizedBox(width: 16),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Custom Travel', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
                          SizedBox(height: 4),
                          Text('Request a tailored package', style: TextStyle(color: Colors.white70, fontSize: 13)),
                        ],
                      ),
                    ),
                    const Icon(Icons.chevron_right, color: Colors.white),
                  ],
                ),
              ),
            ),
          ),

          const SizedBox(height: 32),

          // 6. Our Services
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Our Services',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: kSlate800)),
                    TextButton(
                      onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ServicesScreen())),
                      child: const Text('See all',
                          style: TextStyle(color: kPink, fontSize: 14, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                SizedBox(
                  height: 120,
                  child: _servicesLoading
                      ? const Center(child: CircularProgressIndicator(color: kGreen))
                      : _services.isEmpty
                          ? const Center(
                              child: Text('No services configured yet.',
                                  style: TextStyle(color: kSlate400, fontSize: 13)),
                            )
                          : ListView.builder(
                              scrollDirection: Axis.horizontal,
                              itemCount: _services.length,
                              itemBuilder: (context, i) {
                                final s = _services[i];
                                return GestureDetector(
                                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ServicesScreen())),
                                  child: Container(
                                    width: 100,
                                    margin: const EdgeInsets.only(right: 12),
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(color: kSlate200),
                                      boxShadow: [
                                        BoxShadow(
                                            color: Colors.black.withValues(alpha: 0.03),
                                            blurRadius: 8,
                                            offset: const Offset(0, 4))
                                      ],
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.center,
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(10),
                                          decoration: BoxDecoration(
                                            color: (s['color'] as Color).withValues(alpha: 0.1),
                                            shape: BoxShape.circle,
                                          ),
                                          child: Icon(s['icon'] as IconData,
                                              color: s['color'] as Color, size: 22),
                                        ),
                                        const SizedBox(height: 10),
                                        Text(s['title'] as String,
                                            style: const TextStyle(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 12,
                                                color: kSlate800),
                                            maxLines: 2,
                                            overflow: TextOverflow.ellipsis,
                                            textAlign: TextAlign.center),
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 32),

          // 7. Tour Packages
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Tour Packages',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: kSlate800)),
                    TextButton(
                      onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const TourPackagesScreen())),
                      child: const Text('See all',
                          style: TextStyle(color: kPink, fontSize: 14, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                if (_toursLoading)
                  const SizedBox(height: 150, child: Center(child: CircularProgressIndicator(color: kGreen)))
                else if (_domesticPackages.isEmpty && _internationalPackages.isEmpty)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 32),
                    decoration: BoxDecoration(color: kSlate100, borderRadius: BorderRadius.circular(16)),
                    child: const Center(
                      child: Text('Coming Soon',
                          style: TextStyle(color: kSlate500, fontWeight: FontWeight.bold, fontSize: 16)),
                    ),
                  )
                else
                  Column(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          color: kSlate100,
                          borderRadius: BorderRadius.circular(100),
                        ),
                        child: TabBar(
                          controller: _tourTabController,
                          indicator: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(100),
                            boxShadow: [
                              BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4, offset: const Offset(0, 2))
                            ]
                          ),
                          indicatorSize: TabBarIndicatorSize.tab,
                          labelColor: kGreen,
                          unselectedLabelColor: kSlate500,
                          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          tabs: const [Tab(text: 'Domestic'), Tab(text: 'International')],
                        ),
                      ),
                      const SizedBox(height: 16),
                      SizedBox(
                        height: 220,
                        child: TabBarView(
                          controller: _tourTabController,
                          children: [
                            _PackageHorizontalList(packages: _domesticPackages),
                            _PackageHorizontalList(packages: _internationalPackages),
                          ],
                        ),
                      ),
                    ],
                  ),
              ],
            ),
          ),
          const SizedBox(height: 40),
        ],
      ),
    );
  }";

        string modernServiceCard = @"
class _ModernServiceCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;
  
  const _ModernServiceCard({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: color.withValues(alpha: 0.15), width: 1.5),
          boxShadow: [
            BoxShadow(
              color: color.withValues(alpha: 0.05),
              blurRadius: 10,
              offset: const Offset(0, 4),
            )
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, color: color, size: 28),
            ),
            const SizedBox(height: 16),
            Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: kSlate800)),
            const SizedBox(height: 4),
            Text(subtitle, style: const TextStyle(fontSize: 12, color: kSlate500)),
          ],
        ),
      ),
    );
  }
}
";
        text = Regex.Replace(text, buildPattern, newBuild);
        
        // Find end of main.dart (or end of _HomeScreenState) and append ModernServiceCard
        // We can just append it before class _ServiceCard
        string serviceCardPattern = @"class _ServiceCard extends";
        text = text.Replace(serviceCardPattern, modernServiceCard + "\n\nclass _ServiceCard extends");

        File.WriteAllText(path, text);
        Console.WriteLine("Successfully replaced HomeScreen.build and added _ModernServiceCard!");
    }
}
