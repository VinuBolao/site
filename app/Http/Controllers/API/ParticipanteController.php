<?php

namespace Bolao\Http\Controllers\API;

use Bolao\Models\User;
use Bolao\Models\Bolao;
use Bolao\Models\Participante;
use Bolao\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParticipanteController extends Controller
{
    private $user;
    private $bolao;
    private $participante;

    public function __construct(User $user, Bolao $bolao, Participante $participante)
    {
        $this->user = $user;
        $this->bolao = $bolao;
        $this->participante = $participante;
    }

    public function get($participanteId = null)
    {
        if($participanteId){
            return response()->json($this->participante->with('user')->findOrFail($participanteId));
        } else {
            return response()->json($this->participante->with('user')->get());
        }
    }

    public function getBolao($bolaoId = null)
    {
        return response()->json($this->participante->with('user')->where('bolao_id', $bolaoId)->get());
    }

    public function getRanking($rodada = null)
    {
        $bolao = $this->bolao->where('ativo', 1)->orderByDesc('id')->first();

        $sql = 'u.name,
            SUM(CASE
                WHEN (j.placar_casa = p.palpite_casa) AND (j.placar_fora = p.palpite_fora) THEN 1
                WHEN (j.placar_casa - j.placar_fora = 0) AND (p.palpite_casa - p.palpite_fora = 0) THEN 0
                WHEN (j.placar_casa - j.placar_fora > 0) AND (p.palpite_casa - p.palpite_fora > 0) THEN 0
                WHEN (j.placar_casa - j.placar_fora < 0) AND (p.palpite_casa - p.palpite_fora < 0) THEN 0
                ELSE 0
            END) AS placarexato,
            SUM(CASE
                WHEN (j.placar_casa = p.palpite_casa) AND (j.placar_fora = p.palpite_fora) THEN 0
                WHEN (j.placar_casa - j.placar_fora = 0) AND (p.palpite_casa - p.palpite_fora = 0) THEN 1
                WHEN (j.placar_casa - j.placar_fora > 0) AND (p.palpite_casa - p.palpite_fora > 0) THEN 1
                WHEN (j.placar_casa - j.placar_fora < 0) AND (p.palpite_casa - p.palpite_fora < 0) THEN 1
                ELSE 0
            END) AS placarvencedor,
            SUM(
            CASE 
                WHEN (j.rodada >= b.rodada_dobro) THEN 
                    CASE
                        WHEN (j.placar_casa = p.palpite_casa) AND (j.placar_fora = p.palpite_fora) THEN b.placar_exato * 2 
                        WHEN (j.placar_casa - j.placar_fora = 0) AND (p.palpite_casa - p.palpite_fora = 0) THEN b.placar_vencedor * 2 
                        WHEN (j.placar_casa - j.placar_fora > 0) AND (p.palpite_casa - p.palpite_fora > 0) THEN b.placar_vencedor * 2 
                        WHEN (j.placar_casa - j.placar_fora < 0) AND (p.palpite_casa - p.palpite_fora < 0) THEN b.placar_vencedor * 2 
                        ELSE 0
                    END
                ELSE
                    CASE
                        WHEN (j.placar_casa = p.palpite_casa) AND (j.placar_fora = p.palpite_fora) THEN b.placar_exato 
                        WHEN (j.placar_casa - j.placar_fora = 0) AND (p.palpite_casa - p.palpite_fora = 0) THEN b.placar_vencedor 
                        WHEN (j.placar_casa - j.placar_fora > 0) AND (p.palpite_casa - p.palpite_fora > 0) THEN b.placar_vencedor 
                        WHEN (j.placar_casa - j.placar_fora < 0) AND (p.palpite_casa - p.palpite_fora < 0) THEN b.placar_vencedor 
                        ELSE 0
                    END
                END
            ) AS pontosganhos';

        $ranking = DB::table('palpites AS p')
            ->join('jogos AS j', 'j.id', '=', 'p.jogo_id')
            ->join('users AS u', 'u.id', '=', 'p.user_id')
            ->join('bolaos AS b', 'b.id', '=', 'j.bolao_id')
            ->select(DB::raw($sql))
            ->whereRaw(($rodada) ? "j.campeonato_id = $bolao->campeonato_id AND j.rodada = $rodada" : "j.campeonato_id = $bolao->campeonato_id")
            ->orderBy('pontosganhos', 'DESC')
            ->orderBy('placarexato', 'DESC')
            ->orderBy('placarvencedor', 'DESC')
            ->orderBy('name', 'ASC')
            ->groupBy('u.name')
            ->get();

        return response()->json($ranking, 200);
    }

    public function create(Request $request) {
        $user = $this->user->findOrFail($request->userId);
        $participante = new Participante;
        $participante->user_id = $request->userId;
        $participante->bolao_id = $request->bolaoId;

        if($participante->save()) {
            return response()->json(['success' => true], 200);
        } else {
            return response()->json(['success' => false], 400);
        }
    }

    public function destroy($participanteId) {
        $participante = $this->participante->findOrFail($participanteId);

        if($participante->delete()) {
            return response()->json(['success' => true], 200);
        } else {
            return response()->json(['success' => false], 400);
        }
    }
}
