<?php



namespace App\Http\Controllers;



use App\Models\Invoice;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;



class InvoiceController extends Controller
{
/**
* Display a listing of the invoices.
*/
public function index()
{
$invoices = Invoice::with(['user', 'booking', 'payment'])->get();



return response()->json([
'success' => true,
'message' => 'Invoices fetched successfully',
'data' => $invoices,
]);
}



/**
* Store a newly created invoice in storage.
*/
public function store(StoreInvoiceRequest $request)
{
$invoice = Invoice::create($request->validated());



return response()->json([
'success' => true,
'message' => 'Invoice created successfully',
'data' => $invoice,
]);
}



/**
* Display the specified invoice.
*/
public function show(Invoice $invoice)
{
return response()->json([
'success' => true,
'message' => 'Invoice fetched successfully',
'data' => $invoice->load(['user', 'booking', 'payment']),
]);
}



/**
* Update the specified invoice in storage.
*/
public function update(UpdateInvoiceRequest $request, Invoice $invoice)
{
$invoice->update($request->validated());



return response()->json([
'success' => true,
'message' => 'Invoice updated successfully',
'data' => $invoice,
]);
}



/**
* Remove the specified invoice from storage.
*/
public function destroy(Invoice $invoice)
{
$invoice->delete();



return response()->json([
'success' => true,
'message' => 'Invoice deleted successfully',
]);
}
}
