<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Exports\TransactionsExport;
use App\Imports\TransactionsImport;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('user')->simplePaginate(30);

        return view('home', compact('transactions'));
    }

    public function export()
    {
        return Excel::download(new TransactionsExport, 'transactions.csv');
    }
    
    public function import(Request $request)
    {
        try {
            $request->validate([
                'import_file' => 'required',
            ]);
    
            Excel::import(new TransactionsImport, request()->file('import_file'));
    
            return response()->json(['status' => true], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
       
    }
}


 // return back()->withStatus('Import done!');
