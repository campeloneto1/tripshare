<?php

namespace Tests\Unit;

use App\Models\Place;
use App\Repositories\PlaceRepository;
use App\Services\PlaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceServiceTest extends TestCase
{
    use RefreshDatabase;

    private PlaceService $placeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->placeService = app(PlaceService::class);
    }

    public function test_creates_new_place_with_valid_data(): void
    {
        $placeData = [
            'xid' => 'test_xid_123',
            'name' => 'Torre Eiffel',
            'type' => 'attraction',
            'lat' => 48.858370,
            'lon' => 2.294481,
            'source_api' => 'opentripmap',
        ];

        $placeId = $this->placeService->createOrGetPlace($placeData);

        $this->assertIsInt($placeId);
        $this->assertDatabaseHas('places', [
            'xid' => 'test_xid_123',
            'name' => 'Torre Eiffel',
        ]);
    }

    public function test_returns_existing_place_if_xid_already_exists(): void
    {
        $existingPlace = Place::create([
            'xid' => 'existing_xid',
            'name' => 'Existing Place',
        ]);

        $placeData = [
            'xid' => 'existing_xid',
            'name' => 'Different Name',
            'type' => 'restaurant',
        ];

        $placeId = $this->placeService->createOrGetPlace($placeData);

        $this->assertEquals($existingPlace->id, $placeId);

        // Verifica que não foi criado um novo registro
        $this->assertEquals(1, Place::where('xid', 'existing_xid')->count());

        // O nome original deve ser mantido
        $this->assertDatabaseHas('places', [
            'xid' => 'existing_xid',
            'name' => 'Existing Place',
        ]);
    }

    public function test_throws_exception_if_xid_is_missing(): void
    {
        $placeData = [
            'name' => 'Place Without XID',
            'type' => 'attraction',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('XID é obrigatório para criar um place');

        $this->placeService->createOrGetPlace($placeData);
    }

    public function test_handles_optional_fields(): void
    {
        $placeData = [
            'xid' => 'minimal_xid',
            'name' => 'Minimal Place',
        ];

        $placeId = $this->placeService->createOrGetPlace($placeData);

        $place = Place::find($placeId);

        $this->assertEquals('Minimal Place', $place->name);
        $this->assertNull($place->type);
        $this->assertNull($place->lat);
        $this->assertNull($place->lon);
    }

    public function test_creates_place_with_all_location_data(): void
    {
        $placeData = [
            'xid' => 'full_data_xid',
            'name' => 'Complete Place',
            'type' => 'restaurant',
            'lat' => 40.7128,
            'lon' => -74.0060,
            'source_api' => 'google',
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10001',
            'country' => 'USA',
        ];

        $placeId = $this->placeService->createOrGetPlace($placeData);

        $this->assertDatabaseHas('places', [
            'xid' => 'full_data_xid',
            'name' => 'Complete Place',
            'address' => '123 Main St',
            'city' => 'New York',
            'country' => 'USA',
        ]);
    }
}
