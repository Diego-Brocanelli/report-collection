<?php

namespace ReportCollection\Http\Controllers;

use Illuminate\Http\Request;

class ExampleController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('report-collection::index');
    }

    public function download($format)
    {
        return view('report-collection::index');
    }
}
