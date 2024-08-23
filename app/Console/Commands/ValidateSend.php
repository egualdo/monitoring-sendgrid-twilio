<?php

namespace App\Console\Commands;

use App\Models\HistoryEvent;
use Illuminate\Console\Command;
use App\Http\Controllers\SendGridController;

class ValidateSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'validate:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'validate send 1hr waiting';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
            $searching=HistoryEvent::where('event','delivered')->get();//buscamos el listado de los mails entregados para determinar si
            //se envian mensajes posteriores a 1 hora de haberlos recibids y abiertos y no abiertos

            foreach ($searching as  $value) {
                $deliveredId=$value->sg_message_id;

                $opened=HistoryEvent::  where('event','open')->//tiene abierto el mesaje en su historial?
                                        where('sg_message_id',$deliveredId)->
                                        where('email',$value->email)->
                                        get();

                if(!count($opened)>0){//si no tiene ninguno abierto
                        $actual_start_at = Carbon::parse($value->created_at);
                        $actual_end_at   =  Carbon::now();
                        $difference      = ($actual_end_at->diffInMinutes($actual_start_at, true))/60;//1 hrs

                        if($difference >= 1 ){
                             $before=HistoryEvent:: where('event','delivered')->//contamos para saber que mail de los 3 le debemos mandar
                                                    // where('sg_message_id',$deliveredId)->
                                                    where('email',$value->email)->
                                                    get();

                            if(count($before)>0 && count($before)<3){
                                $forSend=count($before)+1;
                                $send=SendGridController::sendMails($value->email,$value->sg_message_id,$value->event,$forSend);
                            }
                        }
                }else{
                    //si el mensaje lo tiene abierto determinamos desde esa fecha para ver si ya paso 1 hora y enviarle el otro mail
                        $actual_start_at = Carbon::parse($opened->created_at);
                        $actual_end_at   =  Carbon::now();
                        $difference      = ($actual_end_at->diffInMinutes($actual_start_at, true))/60;//1 hrs

                       


                       if($difference >= 1 ){
                              $open=HistoryEvent:: where('event','open')->//contamos para saber que mail de los 3 le debemos mandar
                                                    // where('sg_message_id',$deliveredId)->
                                                    where('email',$value->email)->
                                                    get();

                            if(count($open)>0 && count($open)<3){
                                $forSend=count($open)+1;//se envia el mensaje posterior al que tiene registrado previamente
                                $send=SendGridController::sendMails($value->email,$value->sg_message_id,$value->event,$forSend);
                            }
                        }
                }
            }

         

                       
        // return 0;
    }
}
