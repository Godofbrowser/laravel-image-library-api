<?php

use Illuminate\Database\Seeder;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class FirstPartyClientSeeder extends Seeder
{
	/**
	 * @var \Laravel\Passport\ClientRepository
	 */
	private $client;

	/**
	 * FirstPartyClientSeeder constructor.
	 *
	 * @param \Laravel\Passport\ClientRepository $client
	 */
	public function __construct(ClientRepository $client)
	{
		$this->client = $client;
	}

	/**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$name = 'First party web application'; // Todo:: set as config
    	if (! Passport::client()->where('name', $name)->exists()) {
			$this->client->create(null, $name, url('/'), false, true)
			->update(['secret' => 'kBgCySW4IoYlyk5YeapGWabeSWfwEEQ2i0I65Pyx']);
			$this->command->comment('Client created: '. $name);
		}
    }
}
