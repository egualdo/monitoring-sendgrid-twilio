<?php
namespace App\Http\Controllers;

use App\Models\HistoryEvent;
use App\Models\Post;
use App\Models\Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SendGrid\Mail\From;
use SendGrid\Mail\Mail;
use SendGrid\Mail\To;

class SendGridController extends Controller
{
      


        //Remember to include your own email address in the $mails array (in the sendMails method)
        //and update the $from domain when testing so you can receive the email also.
        

        public function receiveEmailResponse(Request $request) {

                    $email = $request[0]['email'];
                    $sg_message_id = $request[0]['sg_message_id'];
                    $event = $request[0]['event'];

                    try {
                            if($event=='delivered'){
                                $history = HistoryEvent::create([
                                    'email' => $email,
                                    'sg_message_id' => $sg_message_id,
                                    'event' => $event,
                                ]);
                            }

                            if($event=='open'){
                                $find=HistoryEvent::where('event','open')->//buscamos el mas reciente evento open, del mensaje
                                                    where('sg_message_id',$sg_message_id)->
                                                    where('email',$email)->latest()->
                                                    first();

                                if($find){//si ya lo abrio anteriormente!
                                        //logica del cron pero aca tambien se corrobora al momento del evento
                                        //si el ultimo evento es delivered y el mensaje id es el mismo del request ,entonces validamos:

                                                $actual_start_at = Carbon::parse($find->created_at);//fecha en que lo abrio
                                                $actual_end_at   =  Carbon::now();
                                                $difference      = ($actual_end_at->diffInMinutes($actual_start_at, true))/60;//1 hrs

                                                if($difference >= 1){//si la diferencia es mas de 1 hora desde que lo abrio

                                                    //se usa para contar los elementos delivered abajo
                                                    $totalEventsDelivered=HistoryEvent::where('event','delivered')->
                                                                                        where('email',$email)->
                                                                                        // where('sg_message_id',$sg_message_id)->
                                                                                        get();

                                                    $countTotalEventsDelivered=count($totalEventsDelivered);//cuantos eventos delivered tiene ese user
                                                
                                                    //guardamos el evento nuevo de que si abrio el mensaje con id sg_message_id
                                                    $history = HistoryEvent::create([
                                                                                'email' => $email,
                                                                                'sg_message_id' => $sg_message_id,
                                                                                'event' => $event
                                                                            ]);

                                                    if($countTotalEventsDelivered>0 && $countTotalEventsDelivered<3){
                                                        $forSend=$countTotalEventsDelivered+1;//se envia el mensaje posterior al que tiene registrado previamente
                                                         $this->sendMails($email,$sg_message_id,$event,$forSend);
                                                    }

                                                }
                                 
                                }else{
                                            //buscamos el mas reciente evento delivered de ese mensaje 
                                                $find2=HistoryEvent::where('event','delivered')->
                                                                    where('sg_message_id',$sg_message_id)->
                                                                    where('email',$email)->latest()->
                                                                    first();


                                                if($find2){//si encuentra el elemento delivered
                                                    $actual_start_at = Carbon::parse($find2->created_at);
                                                    $actual_end_at   =  Carbon::now();
                                                    $difference      = ($actual_end_at->diffInMinutes($actual_start_at, true))/60;//1 hrs

                                                    if($difference >= 1){//la diff es mayor o igual a 1 hora , se guarda el evento open nuevo
                                                         //guardamos el evento de que si abrio el mensaje con id sg_message_id
                                                        $history = HistoryEvent::create([
                                                                                    'email' => $email,
                                                                                    'sg_message_id' => $sg_message_id,
                                                                                    'event' => $event
                                                                                ]);

                                                        //se usa para contar abajo cuantos eventos delivered tiene ese user
                                                        $totalEventsDelivered=HistoryEvent::where('event','delivered')->
                                                                                            where('email',$email)->
                                                                                            // where('sg_message_id',$sg_message_id)->
                                                                                            get();

                                                        $countTotalEventsDelivered=count($totalEventsDelivered);
                                                       
                                                        if($countTotalEventsDelivered>0 && $countTotalEventsDelivered<3){
                                                            $forSend=$countTotalEventsDelivered+1;//se envia el mensaje posterior al que tiene registrado previamente
                                                            $this->sendMails($email,$sg_message_id,$event,$forSend);
                                                        }
                                                    }else{
                                                         $history = HistoryEvent::create([
                                                                                    'email' => $email,
                                                                                    'sg_message_id' => $sg_message_id,
                                                                                    'event' => $event
                                                                                ]);
                                                    }
                                                }else{
                                                    //   HistoryEvent::create([
                                                    //                     'email' => $email,
                                                    //                     'sg_message_id' => $sg_message_id,
                                                    //                     'event' => 'delivered',
                                                    //                 ]);

                                                 
                                                     HistoryEvent::create([
                                                                            'email' => $email,
                                                                            'sg_message_id' => $sg_message_id,
                                                                            'event' => $event
                                                                                ]);

                                                }

                                               
                                }
                            }


                            return response()->json(["success"=>true,"message"=>$request->all()], 200);

                    } catch (\Throwable $th) {
                        return response()->json(["error"=>true,"message"=>$th->getMessage()], 500);
                    }             
        }

                      

            // in any case, return a 200 OK response so SendGrid knows we are done.
            // return response()->json(["success" => true,"message"=>$request->all()]);

        

        public function validatingSend(HistoryEvent $he){
            if($he->event == "delivered" && $he->sg_message_id ){

            }

                        $created = new Carbon($he->created_at);
                        $now = Carbon::now();
                        $difference = ($created->diff($now)->days < 1)
                            ? 0
                            : $created->diffForHumans($now);

                        
                        if($difference >= 2 && $difference < 4){
                            $this->sendMails();
                        }
        }

        public function sendMails($email,$sg_message_id,$event,$id) {
            // $post = Post::findOrFail($id);
            dd("dentro del send mail:",$email,$sg_message_id,$event,$id);

            $arr=["SG Inbound Tutorial1","SG Inbound Tutorial2","SG Inbound Tutorial3"];

            $mails = [
                "colmenares203@gmail.com",
            ];
            $subject = $arr[$id-1];
            $from = "replies"."@inbound2.veporloquequieres.com.mx";
            $text = "Reply to this email to leave a comment on ";

            $mail = new Mail();
            $sender = new From($from, $arr[$id-1]);
            $recipients = [];
            foreach ($mails as $addr) {
                $recipients[] = new To($addr);
            }
            $mail->setFrom($sender);
            $mail->setSubject($subject);
            $mail->addTos($recipients);
            $mail->addContent("text/plain", $text);
            $sg = new \SendGrid(getenv('SENDGRID_API_KEY'));
            try {
                $response = $sg->send($mail);
                $context = json_decode($response->body());
                if ($response->statusCode() == 202) {
                    echo "Emails have been sent out successfully!";
                     return response()->json(['success'=>"Emails have been sent out successfully!"], 200);
                }else {
                    echo "Failed to send email";
                    return response()->json(['error'=>"Failed to send email","context" => $context], 200);
                    // Log::error("Failed to send email", ["context" => $context]);
                }
            } catch (\Throwable $e) {
                // dd("catch:",$e->getMessage());
                return response()->json(["error"=>$e->getMessage()], 500);
                // Log::error($e);
            }
        }



   
}