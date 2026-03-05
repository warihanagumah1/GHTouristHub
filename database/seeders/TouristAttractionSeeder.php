<?php

namespace Database\Seeders;

use App\Models\TouristAttraction;
use App\Models\TouristRegion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TouristAttractionSeeder extends Seeder
{
    /**
     * Seed Ghana regions and potential attraction sites.
     */
    public function run(): void
    {
        $regions = $this->regionsData();

        foreach ($regions as $regionName => $payload) {
            $regionSlug = Str::slug($regionName);

            $region = TouristRegion::updateOrCreate(
                ['slug' => $regionSlug],
                [
                    'name' => $regionName,
                    'overview' => $payload['overview'],
                    'is_published' => true,
                ]
            );

            foreach ($payload['attractions'] as $index => $attraction) {
                $attractionSlug = Str::slug($attraction['name'].'-'.$regionName);

                TouristAttraction::updateOrCreate(
                    ['slug' => $attractionSlug],
                    [
                        'tourist_region_id' => $region->id,
                        'name' => $attraction['name'],
                        'city' => $attraction['city'] ?? null,
                        'summary' => $attraction['summary'] ?? null,
                        'description' => $attraction['description'] ?? null,
                        'is_featured' => (bool) ($attraction['is_featured'] ?? false),
                        'sort_order' => ($index + 1) * 10,
                        'is_published' => true,
                    ]
                );
            }
        }
    }

    /**
     * @return array<string, array{overview: string, attractions: array<int, array{name: string, city: string, summary: string, description: string, is_featured?: bool}>}>
     */
    private function regionsData(): array
    {
        return [
            'Ahafo' => [
                'overview' => 'Ahafo offers forest reserves, cocoa landscapes, and community eco-tourism opportunities.',
                'attractions' => [
                    ['name' => 'Tano Boase Sacred Grove', 'city' => 'Tano Boase', 'summary' => 'Sacred grove known for caves and cultural heritage.', 'description' => 'A culturally significant grove with rock formations and local stories tied to Bono traditions.', 'is_featured' => true],
                    ['name' => 'Kukuom Forest Trail', 'city' => 'Kukuom', 'summary' => 'Guided nature trails through rich forest terrain.', 'description' => 'Potential eco-tourism site suitable for hiking, birdwatching, and conservation education.'],
                    ['name' => 'Goaso Cocoa Experience', 'city' => 'Goaso', 'summary' => 'Farm-to-market cocoa tourism concept.', 'description' => 'Visitors can explore cocoa farms, local processing stories, and agri-tourism activities.'],
                ],
            ],
            'Ashanti' => [
                'overview' => 'Ashanti combines royal history, lakeside relaxation, and vibrant cultural markets.',
                'attractions' => [
                    ['name' => 'Manhyia Palace Museum', 'city' => 'Kumasi', 'summary' => 'Historic palace museum of the Asante Kingdom.', 'description' => 'A core heritage destination with royal artifacts and history exhibits.', 'is_featured' => true],
                    ['name' => 'Lake Bosomtwe', 'city' => 'Abono', 'summary' => 'Scenic crater lake for leisure and cultural tours.', 'description' => 'A natural freshwater lake suited for day trips, kayaking, and lakeside retreats.', 'is_featured' => true],
                    ['name' => 'Kejetia Cultural Market Walk', 'city' => 'Kumasi', 'summary' => 'Large traditional market and craft exploration.', 'description' => 'Urban cultural route highlighting commerce, kente, and local cuisine.'],
                ],
            ],
            'Bono' => [
                'overview' => 'Bono features waterfalls, wildlife areas, and deep historical sites.',
                'attractions' => [
                    ['name' => 'Kintampo Waterfalls', 'city' => 'Kintampo', 'summary' => 'Tiered waterfall site with forest surroundings.', 'description' => 'A popular nature destination for recreation and eco-tourism.', 'is_featured' => true],
                    ['name' => 'Bui National Park Gateway', 'city' => 'Bui', 'summary' => 'Wildlife and river safari opportunities.', 'description' => 'Potential base for park tours, conservation trips, and educational visits.'],
                    ['name' => 'Fiema Monkey Sanctuary', 'city' => 'Fiema', 'summary' => 'Community-protected monkey sanctuary.', 'description' => 'Conservation tourism destination managed with local participation.'],
                ],
            ],
            'Bono East' => [
                'overview' => 'Bono East is known for riverfront landscapes, fisheries, and community attractions.',
                'attractions' => [
                    ['name' => 'Volta Lake Shoreline (Yeji)', 'city' => 'Yeji', 'summary' => 'Lakeside tourism and boating potential.', 'description' => 'Scenic area suitable for water recreation, fish market tours, and leisure stays.', 'is_featured' => true],
                    ['name' => 'Atebubu Cultural Heritage Center', 'city' => 'Atebubu', 'summary' => 'Proposed center for local arts and traditions.', 'description' => 'Potential cultural attraction focused on dance, craft, and oral history.'],
                    ['name' => 'Prang Weaving & Craft Village', 'city' => 'Prang', 'summary' => 'Traditional weaving and craft experiences.', 'description' => 'Community-based craft tourism concept with demonstrations and workshops.'],
                ],
            ],
            'Central' => [
                'overview' => 'Central Region anchors Ghana tourism with castles, beaches, and canopy walks.',
                'attractions' => [
                    ['name' => 'Cape Coast Castle', 'city' => 'Cape Coast', 'summary' => 'UNESCO World Heritage slave castle.', 'description' => 'Historic site central to global heritage and educational tourism.', 'is_featured' => true],
                    ['name' => 'Elmina Castle', 'city' => 'Elmina', 'summary' => 'Historic coastal castle and fishing town.', 'description' => 'Heritage destination with rich architecture and coastal culture.', 'is_featured' => true],
                    ['name' => 'Kakum National Park', 'city' => 'Kakum', 'summary' => 'Rainforest park with canopy walkway.', 'description' => 'One of Ghana’s leading eco-tourism sites with biodiversity tours.', 'is_featured' => true],
                ],
            ],
            'Eastern' => [
                'overview' => 'Eastern Region offers mountain scenery, waterfalls, and adventure tourism.',
                'attractions' => [
                    ['name' => 'Boti Falls', 'city' => 'Boti', 'summary' => 'Twin waterfall and forest trails.', 'description' => 'Well-known nature destination with seasonal high-flow views.', 'is_featured' => true],
                    ['name' => 'Aburi Botanical Gardens', 'city' => 'Aburi', 'summary' => 'Botanical heritage gardens and picnic site.', 'description' => 'Relaxed destination ideal for family tourism and events.', 'is_featured' => true],
                    ['name' => 'Akaa Falls', 'city' => 'Akaa', 'summary' => 'Hidden waterfall destination in forest setting.', 'description' => 'Potential adventure tourism site requiring improved access and interpretation.'],
                ],
            ],
            'Greater Accra' => [
                'overview' => 'Greater Accra mixes urban culture, beaches, memorials, and event tourism.',
                'attractions' => [
                    ['name' => 'Kwame Nkrumah Memorial Park', 'city' => 'Accra', 'summary' => 'National memorial and museum complex.', 'description' => 'Major heritage attraction in central Accra.', 'is_featured' => true],
                    ['name' => 'Labadi Beach', 'city' => 'Accra', 'summary' => 'Popular beach and entertainment strip.', 'description' => 'Strong potential for structured beach leisure and cultural entertainment.', 'is_featured' => true],
                    ['name' => 'James Town Lighthouse District', 'city' => 'Accra', 'summary' => 'Historic coastal neighborhood with arts scene.', 'description' => 'Urban cultural route linking history, murals, and community storytelling.'],
                ],
            ],
            'North East' => [
                'overview' => 'North East Region features savannah ecology, heritage settlements, and festivals.',
                'attractions' => [
                    ['name' => 'Nalerigu Defence Wall', 'city' => 'Nalerigu', 'summary' => 'Historic defensive wall and heritage site.', 'description' => 'Important cultural landmark with tourism interpretation potential.', 'is_featured' => true],
                    ['name' => 'Gambaga Escarpment Viewpoint', 'city' => 'Gambaga', 'summary' => 'Scenic viewpoint over northern landscape.', 'description' => 'Potential site for photography, hiking, and local guide services.'],
                    ['name' => 'Walewale Cultural Grounds', 'city' => 'Walewale', 'summary' => 'Festival and community cultural site.', 'description' => 'Event tourism opportunity with regional dance and craft showcases.'],
                ],
            ],
            'Northern' => [
                'overview' => 'Northern Region has historic mosques, wildlife corridors, and cultural compounds.',
                'attractions' => [
                    ['name' => 'Mole National Park Access Hub', 'city' => 'Larabanga', 'summary' => 'Primary gateway to Mole safaris.', 'description' => 'Wildlife tourism destination for game drives and eco-lodges.', 'is_featured' => true],
                    ['name' => 'Larabanga Mosque', 'city' => 'Larabanga', 'summary' => 'Ancient mosque and cultural heritage site.', 'description' => 'One of Ghana’s oldest mosques with high heritage value.', 'is_featured' => true],
                    ['name' => 'Tamale Cultural Center', 'city' => 'Tamale', 'summary' => 'Regional arts and craft center.', 'description' => 'Urban tourism point for local craft markets and performances.'],
                ],
            ],
            'Oti' => [
                'overview' => 'Oti Region includes mountain landscapes, river systems, and eco-cultural communities.',
                'attractions' => [
                    ['name' => 'Kyabobo National Park', 'city' => 'Nkwanta', 'summary' => 'Mountain biodiversity and trekking park.', 'description' => 'Eco-tourism hotspot suitable for guided hikes and wildlife watching.', 'is_featured' => true],
                    ['name' => 'Dambai Riverfront', 'city' => 'Dambai', 'summary' => 'Volta-side waterfront recreation zone.', 'description' => 'Potential for boating, waterfront dining, and community events.'],
                    ['name' => 'Nkonya Cultural Trail', 'city' => 'Nkonya', 'summary' => 'Community trail for history and traditions.', 'description' => 'Localized cultural tourism route with storytelling and heritage sites.'],
                ],
            ],
            'Savannah' => [
                'overview' => 'Savannah Region offers game reserves, caves, and broad nature tourism potential.',
                'attractions' => [
                    ['name' => 'Mole National Park Core', 'city' => 'Daboya', 'summary' => 'Largest protected wildlife area in Ghana.', 'description' => 'Premier safari destination with lodging and ranger-led activities.', 'is_featured' => true],
                    ['name' => 'Daboya Smock Weaving Village', 'city' => 'Daboya', 'summary' => 'Traditional weaving and craft demonstrations.', 'description' => 'Cultural attraction showcasing northern textile heritage.'],
                    ['name' => 'Buipe River Crossing', 'city' => 'Buipe', 'summary' => 'Historic river crossing and market area.', 'description' => 'Potential stopover attraction for transit and local trade culture.'],
                ],
            ],
            'Upper East' => [
                'overview' => 'Upper East blends ancient architecture, sacred sites, and dryland eco-tourism.',
                'attractions' => [
                    ['name' => 'Paga Crocodile Pond', 'city' => 'Paga', 'summary' => 'Iconic crocodile sanctuary with local guides.', 'description' => 'Major attraction where visitors observe and learn about crocodile traditions.', 'is_featured' => true],
                    ['name' => 'Tongo Hills', 'city' => 'Tongo', 'summary' => 'Granite landscapes with spiritual heritage.', 'description' => 'Scenic and cultural area for hikes and local storytelling tours.', 'is_featured' => true],
                    ['name' => 'Sirigu Pottery & Art Center', 'city' => 'Sirigu', 'summary' => 'Community art and pottery tourism.', 'description' => 'Hands-on craft destination supporting women-led enterprises.'],
                ],
            ],
            'Upper West' => [
                'overview' => 'Upper West has old architecture, wildlife, and strong community tourism prospects.',
                'attractions' => [
                    ['name' => 'Wa Naa Palace Precinct', 'city' => 'Wa', 'summary' => 'Historic palace and cultural grounds.', 'description' => 'Traditional governance center with heritage tourism potential.', 'is_featured' => true],
                    ['name' => 'Wechiau Hippo Sanctuary', 'city' => 'Wechiau', 'summary' => 'Community sanctuary for hippo viewing.', 'description' => 'Eco-tourism model with guided canoe experiences and conservation focus.', 'is_featured' => true],
                    ['name' => 'Nandom Cathedral & Mission Trail', 'city' => 'Nandom', 'summary' => 'Religious heritage and architecture route.', 'description' => 'Potential faith and heritage tourism circuit in Upper West.'],
                ],
            ],
            'Volta' => [
                'overview' => 'Volta Region is rich in waterfalls, mountain adventures, and lake tourism.',
                'attractions' => [
                    ['name' => 'Wli Waterfalls', 'city' => 'Wli', 'summary' => 'Tallest waterfall destination in West Africa.', 'description' => 'Major attraction with hiking trails and forest biodiversity.', 'is_featured' => true],
                    ['name' => 'Mount Afadja', 'city' => 'Liati Wote', 'summary' => 'Popular mountain trekking destination.', 'description' => 'Adventure tourism hotspot for guided climbs and eco-stays.', 'is_featured' => true],
                    ['name' => 'Tafi Atome Monkey Sanctuary', 'city' => 'Tafi Atome', 'summary' => 'Community sanctuary for Mona monkeys.', 'description' => 'Conservation-based cultural and nature tourism site.'],
                ],
            ],
            'Western' => [
                'overview' => 'Western Region combines beaches, forts, rainforests, and marine tourism potential.',
                'attractions' => [
                    ['name' => 'Nzulezu Stilt Village', 'city' => 'Beyin', 'summary' => 'Unique stilt settlement on water.', 'description' => 'Iconic heritage attraction accessed by canoe through wetland habitat.', 'is_featured' => true],
                    ['name' => 'Fort Apollonia Museum', 'city' => 'Beyin', 'summary' => 'Historic fort and museum complex.', 'description' => 'Coastal heritage site linked to regional history and trade.', 'is_featured' => true],
                    ['name' => 'Busua Beach Surf Zone', 'city' => 'Busua', 'summary' => 'Surf and beach tourism destination.', 'description' => 'Leisure tourism area with hotels, surf schools, and local nightlife.'],
                ],
            ],
            'Western North' => [
                'overview' => 'Western North is known for forest reserves, cocoa belts, and eco-lodge opportunities.',
                'attractions' => [
                    ['name' => 'Ankasa Conservation Area', 'city' => 'Aowin', 'summary' => 'Dense rainforest conservation destination.', 'description' => 'Potential for premium eco-tourism and biodiversity research visits.', 'is_featured' => true],
                    ['name' => 'Sefwi Wiawso Green Corridor', 'city' => 'Sefwi Wiawso', 'summary' => 'Forest corridor for canopy and nature tours.', 'description' => 'Proposed eco-adventure route with birding and hiking activities.'],
                    ['name' => 'Bibiani Mining Heritage Stop', 'city' => 'Bibiani', 'summary' => 'Industrial and local history interpretation site.', 'description' => 'Potential educational attraction around resource history and local development.'],
                ],
            ],
        ];
    }
}
