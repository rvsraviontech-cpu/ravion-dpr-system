<?php

namespace App\Http\Controllers;

use App\Models\Rfq;
use Illuminate\Support\Facades\DB;
use TCPDF;

class RfqEditablePdfController extends Controller
{
    public function download(Rfq $rfq, int $invitation)
    {
        $user = auth()->user();
        abort_unless($user && $user->isActive() && $user->hasPermission('rfqs.view'), 403);

        $assignment = DB::table('rfq_vendor_invitations')
            ->where('id', $invitation)->where('rfq_id', $rfq->id)->first();
        abort_unless($assignment, 404);
        $vendor = DB::table('vendors')->where('id', $assignment->vendor_id)->first();
        abort_unless($vendor, 404);
        $project = DB::table('projects')->where('id', $rfq->project_id)->first();
        $items = DB::table('rfq_invitation_items as ai')
            ->join('rfq_items as i', 'i.id', '=', 'ai.rfq_item_id')
            ->leftJoin('unit_masters as u', 'u.id', '=', 'i.unit_master_id')
            ->where('ai.rfq_vendor_invitation_id', $assignment->id)
            ->where('i.rfq_id', $rfq->id)
            ->select('i.id', 'i.product_name', 'i.specification_text', 'i.requested_quantity', 'u.unit_code')
            ->orderBy('i.sort_order')->orderBy('i.id')->get();
        abort_if($items->isEmpty(), 404);
        abort_if($items->count() > 100, 422, 'This PDF supports up to 100 assigned materials.');

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Ravion DPR');
        $pdf->SetAuthor('Ravion Homes');
        $pdf->SetTitle('Editable Quotation - '.$rfq->rfq_number);
        $pdf->SetMargins(9, 9, 9);
        $pdf->SetAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setFormDefaultProp(['lineWidth' => 0.2, 'borderStyle' => 'solid', 'fillColor' => [255,255,255], 'strokeColor' => [185,198,211]]);

        $pageWidth = 297;
        $left = 9;
        // Exact width 279 mm, fitting A4 landscape within 9 mm margins.
        $widths = [11, 49, 22, 25, 17, 24, 26, 26, 15, 29, 35];
        $rowsPerPage = 5; // One page for up to 5 materials; larger RFQs paginate safely
        $chunks = $items->chunk($rowsPerPage);
        $rowNumber = 0;
        $lastY = 0;
        $singlePage = $items->count() <= $rowsPerPage;
        $pageCount = $chunks->count();

        foreach ($chunks as $pageIndex => $chunk) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetTextColor(21, 40, 59);
            $pdf->Text(10, 10, 'RAVION');
            $pdf->SetDrawColor(224, 166, 0);
            $pdf->SetLineWidth(0.8);
            $pdf->Line(10, 19, 37, 19);
            $pdf->SetLineWidth(0.2);
            $pdf->SetFont('helvetica', 'B', 13);
            $pdf->SetXY(82, 10);
            $pdf->Cell(135, 8, 'REQUEST FOR QUOTATION', 0, 0, 'C');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetXY(225, 10);
            $pdf->Cell(62, 8, $rfq->rfq_number, 0, 0, 'R');
            $pdf->SetDrawColor(207, 216, 227);
            $pdf->Line(9, 23, 288, 23);

            $deadline = $rfq->submission_deadline
                ? \Illuminate\Support\Carbon::parse($rfq->submission_deadline)->format('d/m/Y h:i A') : '-';
            $this->labelValue($pdf, 10, 27, 95, 'PROJECT', $project->project_name ?? '-');
            $this->labelValue($pdf, 107, 27, 95, 'VENDOR', $vendor->vendor_name);
            $this->labelValue($pdf, 205, 27, 82, 'SUBMISSION DEADLINE', $deadline);
            $pdf->SetFont('helvetica', '', 7.3);
            $pdf->SetTextColor(80, 97, 118);
            $pdf->Text(10, 42, 'Enter prices in the blue-outlined fields. Open in Adobe Acrobat Reader for automatic calculations.');

            $y = 49;
            $headers = ['Sl.No', 'Material Name', 'Requested Quantity', 'MRP Rate Per Unit', 'Discount %', 'Discount Amount', 'Net Rate Per Unit', 'Taxable Amount', 'GST %', 'Net Rate Per Unit with GST', 'Amount with GST'];
            $x = $left;
            foreach ($widths as $i => $w) {
                $pdf->SetFillColor(241, 245, 249);
                $pdf->SetDrawColor(207, 216, 227);
                $pdf->Rect($x, $y, $w, 17, 'DF');
                $pdf->SetFont('helvetica', 'B', 6.6);
                $pdf->SetTextColor(21, 40, 59);
                $pdf->MultiCell($w - 2, 12, $headers[$i], 0, 'C', false, 1, $x + 1, $y + 2, true, 0, false, true, 12, 'M');
                $x += $w;
            }
            $y += 17;
            foreach ($chunk as $item) {
                $rowNumber++;
                $h = 10;
                $x = $left;
                foreach ($widths as $w) {
                    $pdf->SetDrawColor(207, 216, 227);
                    $pdf->Rect($x, $y, $w, $h);
                    $x += $w;
                }
                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetTextColor(21, 40, 59);
                $pdf->Text(12, $y + 4, (string)$rowNumber);
                $name = (string)$item->product_name;
                if ($item->specification_text) $name .= ' / '.$item->specification_text;
                $pdf->SetFont('helvetica', '', 7);
                $pdf->MultiCell(47, 10, $name, 0, 'L', false, 1, 21, $y + 1, true, 0, false, true, 10, 'M');
                $qty = rtrim(rtrim(number_format((float)$item->requested_quantity, 3, '.', ''), '0'), '.');
                $pdf->SetFont('helvetica', '', 7.5);
                $pdf->SetXY(70, $y + 2);
                $pdf->Cell(22, 8, $qty.' '.($item->unit_code ?? ''), 0, 0, 'C');
                $fieldPositions = [3, 4, 5, 6, 7, 8, 9, 10];
                $xPositions = [];
                $pos = $left;
                foreach ($widths as $w) { $xPositions[] = $pos; $pos += $w; }
                foreach ($fieldPositions as $col) {
                    $fieldName = match ($col) {
                        3 => 'mrp_', 4 => 'discount_pct_', 5 => 'discount_amt_',
                        6 => 'net_rate_', 7 => 'taxable_', 8 => 'gst_pct_',
                        9 => 'gst_rate_', 10 => 'line_total_',
                    }.$rowNumber;
                    $editable = in_array($col, [3, 4, 8], true);
                    $pdf->SetXY($xPositions[$col] + 1, $y + 2);
                    $pdf->SetFont('helvetica', '', 7);
                    $pdf->TextField($fieldName, $widths[$col] - 2, 8,
                        ['borderStyle' => 'solid', 'strokeColor' => $editable ? [68, 121, 188] : [232, 237, 243],
                         'fillColor' => $editable ? [246, 250, 255] : [247, 249, 252],
                         'readonly' => !$editable],
                        ['v' => $col === 8 ? '18' : ($editable ? '0' : '')],
                        $xPositions[$col] + 1, $y + 2);
                }
                $y += $h;
            }
            $lastY = $y;
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetTextColor(100, 116, 139);
            if (!$singlePage) $pdf->Text(10, 199, 'Ravion Homes  |  Procurement Team  |  +91 98666 07039  |  info@ravionhomes.com  |  ravionhomes.com');
            if (!$singlePage) $pdf->Text(267, 199, ($pageIndex + 1).' / '.($pageCount + 1));
        }

        // Compact single-page summary for up to 9 materials. Larger RFQs use a summary page.
        if (!$singlePage) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 15);
            $pdf->SetTextColor(21, 40, 59);
            $pdf->Text(12, 12, 'RAVION  |  QUOTATION SUMMARY');
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Text(12, 23, 'RFQ: '.$rfq->rfq_number.'    Vendor: '.$vendor->vendor_name);
            $summaryTop = 55;
            $checkboxY = 34;
        } else {
            $summaryTop = max(115, $lastY + 13);
            $checkboxY = $summaryTop - 10;
            $pdf->SetDrawColor(207, 216, 227);
            $pdf->Line(9, $summaryTop - 13, 288, $summaryTop - 13);
        }
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(21, 40, 59);
        $pdf->Text(12, $checkboxY, 'Additional Charges (INR)');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Text(157, $checkboxY, 'GST Applicable (checked = YES; unchecked = NO)');
        $pdf->CheckBox('gst_enabled', 5.2, true, [], [], 'Yes', 243, $checkboxY - 0.8);

        $charges = ['Auto Charges' => 'auto_charges', 'Hamali Charges' => 'hamali_charges', 'Loading Charges' => 'loading_charges', 'Unloading Charges' => 'unloading_charges'];
        $y = $summaryTop;
        foreach ($charges as $label => $field) {
            $pdf->SetDrawColor(207, 216, 227);
            $pdf->Rect(12, $y, 110, 9);
            $pdf->Rect(122, $y, 23, 9);
            $pdf->SetFont('helvetica', '', 7.8);
            $pdf->Text(15, $y + 3, $label);
            $pdf->TextField($field, 20, 6.5, ['strokeColor' => [68,121,188], 'fillColor' => [246,250,255]], ['v' => '0'], 123.5, $y + 1.1);
            $y += 9;
        }
        $summary = [
            'Gross Product Amount' => 'gross_total',
            'Total Discount Offered' => 'discount_total',
            'Taxable Product Amount' => 'taxable_total',
            'Total GST' => 'gst_total',
            'Total Amount with GST' => 'with_gst_total',
            'Additional Charges' => 'charges_total',
            'Net Total' => 'net_total',
        ];
        $y = $summaryTop;
        foreach ($summary as $label => $field) {
            if ($field === 'net_total') $pdf->SetFillColor(230, 242, 253);
            else $pdf->SetFillColor(250, 252, 255);
            $pdf->SetDrawColor(207, 216, 227);
            $pdf->Rect(157, $y, 88, 8.6, 'DF');
            $pdf->Rect(245, $y, 40, 8.6, 'DF');
            $pdf->SetFont('helvetica', $field === 'net_total' ? 'B' : '', 7.8);
            $pdf->Text(160, $y + 3, $label);
            $pdf->TextField($field, 36, 6.4, ['readonly' => true, 'fillColor' => [247,249,252], 'strokeColor' => [232,237,243]], ['v'=>'0.00'], 247, $y + 1);
            $y += 8.6;
        }
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->MultiCell(132, 18, 'Offline fillable quotation. Save a copy after entering prices. This PDF does not submit to Ravion DPR. For online submission use the secure vendor quotation link.', 0, 'L', false, 1, 12, $summaryTop + 43);
        $pdf->SetDrawColor(207,216,227);
        $pdf->Line(9, 193, 288, 193);
        $pdf->Text(10, 197, 'Ravion Homes  |  Procurement Team  |  +91 98666 07039  |  info@ravionhomes.com  |  ravionhomes.com');
        $pdf->Text(275, 197, $singlePage ? '1 / 1' : ($pageCount + 1).' / '.($pageCount + 1));

        $js = <<<'JS'
// Ravion PDF calculation engine. Adobe Acrobat desktop JavaScript required.
var ravionDocument = this;
var ravionBusy = false;
function ravionNumber(name) {
  var f = ravionDocument.getField(name);
  if (!f) return 0;
  var n = parseFloat(String(f.value).replace(/,/g, ''));
  return isNaN(n) || !isFinite(n) ? 0 : n;
}
function ravionWrite(name, value) {
  var f = ravionDocument.getField(name);
  if (f) f.value = (Math.round((value + 0.000000001) * 100) / 100).toFixed(2);
}
function ravionRecalculate() {
  if (ravionBusy) return;
  ravionBusy = true;
  try {
    var gstCheckbox = ravionDocument.getField('gst_enabled');
    var gstOn = !!gstCheckbox && gstCheckbox.value !== 'Off';
    var gross = 0, discounts = 0, taxableTotal = 0, gstTotal = 0;
    var quantities = __QTYS__;
    for (var i = 1; i <= __COUNT__; i++) {
      var qty = quantities[i-1];
      var mrp = Math.max(0, ravionNumber('mrp_'+i));
      var percent = Math.min(100, Math.max(0, ravionNumber('discount_pct_'+i)));
      var lineGross = Math.round(qty * mrp * 100) / 100;
      var discount = Math.round(lineGross * percent) / 100;
      var taxable = Math.round((lineGross - discount) * 100) / 100;
      var netRate = qty > 0 ? taxable / qty : 0;
      var gstPct = gstOn ? Math.min(100, Math.max(0, ravionNumber('gst_pct_'+i))) : 0;
      var gst = Math.round(taxable * gstPct) / 100;
      ravionWrite('discount_amt_'+i, discount);
      ravionWrite('net_rate_'+i, netRate);
      ravionWrite('taxable_'+i, taxable);
      var rateWithGst = ravionDocument.getField('gst_rate_'+i);
      var lineWithGst = ravionDocument.getField('line_total_'+i);
      var gstInput = ravionDocument.getField('gst_pct_'+i);
      if (gstInput) { gstInput.readonly = !gstOn; gstInput.fillColor = gstOn ? color.white : color.ltGray; }
      if (rateWithGst) rateWithGst.value = gstOn ? (Math.round(netRate*(1+gstPct/100)*100)/100).toFixed(2) : 'N/A';
      if (lineWithGst) lineWithGst.value = gstOn ? (taxable+gst).toFixed(2) : 'N/A';
      gross += lineGross; discounts += discount; taxableTotal += taxable; gstTotal += gst;
    }
    var charges = ravionNumber('auto_charges') + ravionNumber('hamali_charges') + ravionNumber('loading_charges') + ravionNumber('unloading_charges');
    ravionWrite('gross_total', gross);
    ravionWrite('discount_total', discounts);
    ravionWrite('taxable_total', taxableTotal);
    ravionWrite('gst_total', gstTotal);
    ravionWrite('with_gst_total', taxableTotal+gstTotal);
    ravionWrite('charges_total', charges);
    ravionWrite('net_total', taxableTotal+gstTotal+charges);
  } catch (e) { try { console.println('Ravion calculation: '+e); } catch (ignored) {} }
  ravionBusy = false;
}
// Acrobat rejects setAction("Blur") on text fields (GeneralError).
// Use supported Validate actions, and isolate any unsupported action.
var ravionInputs = ['auto_charges','hamali_charges','loading_charges','unloading_charges'];
for (var j=1; j<=__COUNT__; j++) {
  ravionInputs.push('mrp_'+j);
  ravionInputs.push('discount_pct_'+j);
  ravionInputs.push('gst_pct_'+j);
}
for (var k=0; k<ravionInputs.length; k++) {
  var field = ravionDocument.getField(ravionInputs[k]);
  if (field) {
    try { field.setAction('Validate', 'ravionRecalculate();'); }
    catch (e) { console.println('Cannot attach Validate to '+ravionInputs[k]+': '+e); }
  }
}
var gstSwitch = ravionDocument.getField('gst_enabled');
if (gstSwitch) {
  try { gstSwitch.setAction('MouseUp', 'ravionRecalculate();'); }
  catch (e) { console.println('Cannot attach GST click: '+e); }
}
ravionRecalculate();
JS;
        $qtys = $items->map(fn ($item) => (float)$item->requested_quantity)->values()->all();
        $js = str_replace(['__COUNT__', '__QTYS__'], [(string)count($qtys), json_encode($qtys, JSON_THROW_ON_ERROR)], $js);
        $pdf->IncludeJS($js);

        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '_', $rfq->rfq_number.'_vendor_'.$vendor->id.'_editable').'.pdf';
        return response($pdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function labelValue(TCPDF $pdf, float $x, float $y, float $width, string $label, string $value): void
    {
        $pdf->SetTextColor(100, 116, 139);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Text($x, $y, $label);
        $pdf->SetTextColor(21, 40, 59);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($x, $y + 4);
        $pdf->Cell($width, 7, mb_strimwidth($value, 0, 55, '...'), 0, 0, 'L');
    }
}
