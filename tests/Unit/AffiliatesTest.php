<?php

namespace Tests\Unit;

use App\Http\Controllers\AffiliatesController;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AffiliatesTest extends TestCase
{
    public function test_within_distance_working()
    {
        $aff = new AffiliatesController();
        $affWithinDistance = $aff->getAffiliatesWithinDistance();
        $this->assertCount(16, $affWithinDistance);

        $affWithinDistance = $aff->getAffiliatesWithinDistance(500);
        $this->assertCount(32, $affWithinDistance);

        $affWithinDistance = $aff->getAffiliatesWithinDistance(20);
        $this->assertCount(1, $affWithinDistance);
    }

    public function test_check_for_sort_order()
    {
        $aff = new AffiliatesController();
        $affWithinDistance = $aff->getAffiliatesWithinDistance();

        $lastId = 0;
        foreach ($affWithinDistance as $a) {
            if ($a['affiliate_id'] < $lastId) {
                $this->fail('Are not in ascending order :(');
            }
            $lastId = $a['affiliate_id'];
        }
        $this->assertTrue(true);
    }

    public function test_ok_status_homepage()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_file_exists()
    {
        Storage::disk('public')->assertExists('affiliates.txt');
    }

    public function test_correct_distance_between_two_points()
    {
        $aff = new AffiliatesController();
        $distnaceBetweenTwoPoints = $aff->getDistanceBetweenTwoPoints("53.3340285", "-6.2535495", "51.903614", "-8.468399");
        $this->assertEquals(218.27, $distnaceBetweenTwoPoints);

    }

    public function test_distance_over_100()
    {
        $aff = new AffiliatesController();
        $distnaceBetweenTwoPoints = $aff->getDistanceBetweenTwoPoints("53.3340285", "-6.2535495", "41.077747", "1.131593");
        $this->assertGreaterThan(100, $distnaceBetweenTwoPoints);
    }

    public function test_affiliates_render_table()
    {
        $response = $this->get('/');
        $response->assertSee(['Affiliate Id', 'Name', 'Distance (Km)']);
    }
}
