<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;
use Auth;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;

class testLocal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:local';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fb_account=SocialAccount::where(['type'=>'0'])->first();
        $pages=$fb_account->FbPages;
        $response = Http::get("https://graph.facebook.com/".$pages[0]->page_id."?fields=published_posts&access_token&access_token=".$pages[0]->accessToken);
        $response=$response->json();
        Log::info($response);
        return 0;
    }
}
