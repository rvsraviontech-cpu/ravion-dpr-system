<?php
namespace App\Services\ClientControl;
use Illuminate\Support\Facades\Storage;
class DrawingThumbnail {
 public static function response(object $file) {
  $headers=['Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff'];
  $fallback=fn()=>response('<svg xmlns="http://www.w3.org/2000/svg" width="320" height="240" viewBox="0 0 320 240"><rect width="320" height="240" fill="#edf3f8"/><path d="M125 48h48l24 24v102h-72z" fill="white" stroke="#54748d" stroke-width="2"/><path d="M173 48v25h24M139 100h44M139 115h44M139 130h30" fill="none" stroke="#54748d" stroke-width="2"/><text x="160" y="205" text-anchor="middle" font-family="sans-serif" font-size="13" fill="#35536b">Drawing preview</text></svg>',200,$headers+['Content-Type'=>'image/svg+xml']);
  if(!extension_loaded('gd') || !in_array($file->mime_type,['image/jpeg','image/png'],true)) return $fallback();
  $path=Storage::disk('local')->path($file->storage_path); $size=@getimagesize($path);
  if(!$size || $size[0]<1 || $size[1]<1 || $size[0]*$size[1]>12000000 || filesize($path)>20000000) return $fallback();
  $im=$file->mime_type==='image/png'?@imagecreatefrompng($path):@imagecreatefromjpeg($path); if(!$im) return $fallback();
  $ratio=min(320/$size[0],240/$size[1],1); $w=max(1,(int)round($size[0]*$ratio)); $h=max(1,(int)round($size[1]*$ratio));
  $thumb=imagecreatetruecolor(320,240); imagefill($thumb,0,0,imagecolorallocate($thumb,255,255,255)); imagecopyresampled($thumb,$im,(int)((320-$w)/2),(int)((240-$h)/2),0,0,$w,$h,$size[0],$size[1]); ob_start(); imagejpeg($thumb,null,85); $bytes=ob_get_clean(); imagedestroy($im); imagedestroy($thumb); return response($bytes,200,$headers+['Content-Type'=>'image/jpeg']);
 }
}
