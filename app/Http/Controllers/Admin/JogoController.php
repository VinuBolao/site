<?php

namespace Bolao\Http\Controllers\Admin;

use Bolao\Forms\JogoForm;
use Bolao\Models\Jogo;
use Illuminate\Http\Request;
use Bolao\Http\Controllers\Controller;

class JogoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $jogos = Jogo::with('timecasa', 'timefora', 'campeonato')
            ->orderBy('rodada')
            ->orderBy('inicio')
            ->paginate(10);

        return view('admin.jogo.index', compact('jogos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $form = \FormBuilder::create(JogoForm::class, [
            'url' => route('admin.jogo.store'),
            'method' => 'POST'
        ]);

        return view('admin.jogo.add', compact('form'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $form = \FormBuilder::create(JogoForm::class);

        if(!$form->isValid()){
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }

        $jogo = $form->getFieldValues();
        Jogo::create($jogo);

        return redirect()->route('admin.jogo.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \Bolao\Models\Jogo  $jogo
     * @return \Illuminate\Http\Response
     */
    public function show(Jogo $jogo)
    {
        return view('admin.jogo.show', compact('jogo'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Bolao\Models\Jogo  $jogo
     * @return \Illuminate\Http\Response
     */
    public function edit(Jogo $jogo)
    {
        $form = \FormBuilder::create(JogoForm::class, [
            'url' => route('admin.jogo.update', ['jogo' => $jogo->id]),
            'method' => 'PUT',
            'model' => $jogo
        ]);

        return view('admin.jogo.edit', compact('form'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Bolao\Models\Jogo  $jogo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Jogo $jogo)
    {
        $form = \FormBuilder::create(JogoForm::class, [
            'data' => ['campeonato_id' => $jogo->campeonato_id, 'timecasa_id' => $jogo->timecasa_id, 'timefora_id' => $jogo->timefora_id]
        ]);

        if(!$form->isValid()){
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }

        $data = $form->getFieldValues();
        $jogo->update($data);

        return redirect()->route('admin.jogo.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Bolao\Models\Jogo  $jogo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Jogo $jogo)
    {
        $jogo->delete();
        return redirect()->route('admin.jogo.index');
    }
}