<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ClientDrawingPermissionsSeeder extends Seeder {
 public function run(): void {
  $permissions = [
   'client_control.drawings.view' => 'View drawing register',
   'client_control.drawings.create' => 'Register drawings',
   'client_control.drawings.update' => 'Edit draft drawings',
   'client_control.drawings.upload' => 'Upload drawing files',
   'client_control.drawings.revise' => 'Create revisions',
   'client_control.drawings.submit' => 'Submit for verification',
   'client_control.drawings.verify' => 'Verify drawings',
   'client_control.drawings.approve' => 'Approve drawings internally',
   'client_control.drawings.reject' => 'Return drawings for correction',
   'client_control.drawings.publish' => 'Publish drawings to clients',
   'client_control.drawings.withdraw' => 'Withdraw publications',
   'client_control.drawings.download' => 'Download internal files',
   'client_control.drawings.manage_client_downloads' => 'Control client downloads',
   'client_control.drawings.acknowledge' => 'Acknowledge client responses',
   'client_control.drawings.manage_masters' => 'Manage drawing masters',
   'client_control.drawings.export' => 'Export drawing register',
   'client_control.drawings.audit' => 'View drawing audit history',
   'client_control.drawings.archive' => 'Archive drawings',
  ];
  DB::transaction(function () use ($permissions) {
   foreach ($permissions as $name => $description) {
    if (!DB::table('permissions')->where('name',$name)->exists()) DB::table('permissions')->insert(['name'=>$name,'module'=>'Client Control — Construction Drawings','description'=>$description,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
   }
  });
 }
}
