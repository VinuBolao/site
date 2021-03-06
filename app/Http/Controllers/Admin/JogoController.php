<?php

namespace Bolao\Http\Controllers\Admin;

use Bolao\Forms\JogoForm;
use Bolao\Models\Campeonato;
use Bolao\Models\Jogo;
use Illuminate\Http\Request;
use Bolao\Http\Controllers\Controller;

class JogoController extends Controller
{
    public function index(Request $request)
    {
        $campeonatos = Campeonato::all();
        if($request->campeonatoId > 0 && $request->rodada > 0) {
            $jogos = Jogo::with('timecasa', 'timefora', 'campeonato')
                ->where('campeonato_id', $request->campeonatoId)
                ->where('rodada', $request->rodada)
                ->orderBy('rodada')
                ->orderBy('inicio')
                ->paginate(10)
                ->appends(request()->query());
        } elseif($request->campeonatoId > 0 || $request->rodada > 0) {
            $jogos = Jogo::with('timecasa', 'timefora', 'campeonato')
                ->where('campeonato_id', $request->campeonatoId)
                ->orWhere('rodada', $request->rodada)
                ->orderBy('rodada')
                ->orderBy('inicio')
                ->paginate(10)
                ->appends(request()->query());
        } else {
            $jogos = Jogo::with('timecasa', 'timefora', 'campeonato')
                ->orderBy('rodada')
                ->orderBy('inicio')
                ->paginate(10)
                ->appends(request()->query());
        }
        return view('admin.jogo.index', compact('jogos', 'campeonatos'));
    }

    public function create()
    {
        $form = \FormBuilder::create(JogoForm::class, [
            'url' => route('admin.jogo.store'),
            'method' => 'POST'
        ]);
        return view('admin.jogo.add', compact('form'));
    }

    public function store(Request $request)
    {
        $form = \FormBuilder::create(JogoForm::class);
        if(!$form->isValid()){
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }
        $jogo = $form->getFieldValues();
        $jogo['inicio'] = $jogo['inicio'] . " " . $jogo['horario'];
        unset($jogo['horario']);
        Jogo::create($jogo);
        return redirect()->route('admin.jogo.index');
    }

    public function show(Jogo $jogo)
    {
        return view('admin.jogo.show', compact('jogo'));
    }

    public function edit(Jogo $jogo)
    {
        $form = \FormBuilder::create(JogoForm::class, [
            'url' => route('admin.jogo.update', ['jogo' => $jogo->id]),
            'method' => 'PUT',
            'model' => $jogo
        ]);
        return view('admin.jogo.edit', compact('form'));
    }

    public function update(Request $request, Jogo $jogo)
    {
        $form = \FormBuilder::create(JogoForm::class, [
            'data' => ['campeonato_id' => $jogo->campeonato_id, 'timecasa_id' => $jogo->timecasa_id, 'timefora_id' => $jogo->timefora_id]
        ]);
        if(!$form->isValid()){
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }
        $data = $form->getFieldValues();
        $data['inicio'] = $data['inicio'] . " " . $data['horario'];
        unset($data['horario']);
        $jogo->update($data);
        return redirect()->route('admin.jogo.index');
    }

    public function destroy(Jogo $jogo)
    {
        $jogo->delete();
        return redirect()->route('admin.jogo.index');
    }

    public function sincronizar()
    {
        return view('admin.jogo.sincronizar');
    }
}
