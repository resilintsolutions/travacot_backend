<?php
namespace App\Jobs;
use App\Services\HotelbedsService;
use App\Models\Hotel;
use App\Models\SupplierResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncHotelsFromHotelbeds implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    public array $hotelIds;
    public function __construct(array $hotelIds=[]) { $this->hotelIds=$hotelIds; }

    public function handle(HotelbedsService $hb)
    {
        $payload = [
            'stay'=>['checkIn'=>now()->addDays(7)->toDateString(),'checkOut'=>now()->addDays(8)->toDateString()],
            'occupancies'=>[['rooms'=>1,'adults'=>2,'children'=> 0]],
        ];
        if ($this->hotelIds) $payload['hotelIds']=$this->hotelIds;

        $resp = $hb->availability($payload);
        SupplierResponse::create(['supplier'=>'hotelbeds','endpoint'=>'/hotel-api/1.0/hotels','request_payload'=>$payload,'response_body'=>json_encode($resp),'status_code'=>200]);

        $hotels = $resp['hotels'] ?? [];
        foreach ($hotels as $h) {
            $vendor_code = $h['code'] ?? $h['hotelCode'] ?? null;
            if (!$vendor_code) continue;
            $name = $h['name'] ?? $h['hotelName'] ?? 'Unnamed';
            $firstRate = data_get($h,'rooms.0.rates.0');
            Hotel::updateOrCreate(
                ['vendor'=>'hotelbeds','vendor_id'=>$vendor_code],
                ['name'=>$name,'slug'=>\Str::slug($name.'-'.$vendor_code),'country'=>data_get($h,'destination.country'),'city'=>data_get($h,'destination.city'),'lowest_rate'=>$firstRate['net'] ?? null,'currency'=>$firstRate['currency'] ?? null,'meta'=>$h,'status'=>'active']
            );
        }
    }
}
