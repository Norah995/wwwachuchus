<?php

namespace Muserpol\Http\Controllers;
use Muserpol\QuotaAidCertification;
use Illuminate\Http\Request;
use Muserpol\Models\Affiliate;
use Muserpol\Models\City;
use Session;
use Auth;
use Validator;
use Muserpol\Models\Address;
use DateTime;
use Muserpol\User;
use Carbon\Carbon;
use Muserpol\Helpers\Util;
use Muserpol\Models\Voucher;
use Muserpol\Models\VoucherType;
use Muserpol\Models\Spouse;
use Muserpol\Models\Contribution\AidContribution;

class QuotaAidCertificationController extends Controller
{
    //
    public function printQuotaAidCommitmentLetter($id)
    {
        $affiliate = Affiliate::find($id);
        $date = Util::getStringDate(date('Y-m-d'));
        $username = Auth::user()->username;//agregar cuando haya roles
        $city = Auth::user()->city->name;
        // return $city;
        if ($affiliate->affiliate_state->name == "Fallecido") {
            $title = "COMPROMISO DE PAGO - APORTE PARA SOLICITANTES PARA EL PAGO DE APORTE DIRECTO DE LAS (OS) VIUDAS (OS) DEL  SECTOR PASIVO CORRESPONDIENTE AL SISTEMA INTEGRAL DE PENSIONES";
            $spouses = Spouse::where('affiliate_id', $affiliate->id)->first();
            $beneficiary = "en mi calidad de viuda (o)";
            $glosa = "Soy beneficiaria(o) (derechohabiente) con una prestación en curso de pago del Sistema Integral de Pensiones (SIP).";
            $bene = $spouses;
        } else {
            $title = "COMPROMISO DE PAGO - APORTE PARA SOLICITANTES PARA EL PAGO DE APORTE DIRECTO DEL SECTOR PASIVO CORRESPONDIENTE AL SISTEMA INTEGRAL DE PENSIONES";
            $beneficiary = "como miembro del servicio pasivo de la Policía Boliviana";
            $glosa = "Recibo una prestación en curso de pago del Sistema Integral de Pensiones.";
            $bene = $affiliate;
        }
        $pdftitle = "Carta de Compromiso de Auxilio Mortuorio";
        $namepdf = Util::getPDFName($pdftitle, $bene);
        // return view('ret_fun.print.beneficiaries_qualification', compact('date','subtitle','username','title','number','retirement_fund','affiliate','submitted_documents'));
        return \PDF::loadView('quota_aid.print.quota_aid_commitment_letter', compact('date', 'username', 'title', 'glosa', 'bene', 'city', 'beneficiary'))->setPaper('letter')->setOption('encoding', 'utf-8')->setOption('footer-right', 'Pagina [page] de [toPage]')->setOption('footer-left', 'PLATAFORMA VIRTUAL DE LA MUSERPOL - 2018')->stream("$namepdf");
    }

    public function printDirectContributionQuoteAid(Request $request)
    {
        $contributions  = json_decode($request->contributions);     
        $total = $request->total;
        $total_literal = Util::convertir($total);
        $affiliate = Affiliate::find($request->affiliate_id);                                
        $date = Util::getStringDate(date('Y-m-d'));
        $title = "REVISAR";
        $username = Auth::user()->username;//agregar cuando haya roles
        $name_user_complet = Auth::user()->first_name." ".Auth::user()->last_name;        
        $detail = "Pago de aporte directo";
        $beneficiary = $affiliate;
        $name_beneficiary_complet = Util::fullName($beneficiary);
        $pdftitle = "Comprobante";
        $namepdf = Util::getPDFName($pdftitle, $beneficiary);
        $util = new Util();
        return \PDF::loadView(
            'quota_aid.print.affiliate_aid_contribution', 
                compact(
                        'date', 
                        'username', 
                        'title', 
                        'number',  
                        'beneficiary', 
                        'contributions',
                        'total',
                        'total_literal',
                        'detail',
                        'util',
                        'name_user_complet',
                        'name_beneficiary_complet'
                ))
                ->setPaper('letter')
                ->setOption('encoding', 'utf-8')
                ->setOption('footer-right', 'Pagina [page] de [toPage]')
                ->setOption('footer-left', 'PLATAFORMA VIRTUAL DE LA MUSERPOL - 2018')                
                ->stream("$namepdf");
    }  

    public function printVoucherQuoteAid(Request $request, $affiliate_id,$voucher_id)
    {
        $affiliate = Affiliate::find($affiliate_id);
        $voucher = Voucher::find($voucher_id);
        $aid_contributions=[];
        $total_literal = Util::convertir($voucher->total);
        $payment_date = Util::getStringDate($voucher->payment_date);
        $date = Util::getStringDate(date('Y-m-d'));
        $title = "RECIBO";
        $subtitle = "AUXILIO MORTUORIO";
        $username = Auth::user()->username;//agregar cuando haya roles
        $name_user_complet = Auth::user()->first_name . " " . Auth::user()->last_name;
        $number = $voucher->code;
        $descripcion = VoucherType::where('id', $voucher->voucher_type_id)->first();
        $util = new Util();
        if ($affiliate->affiliate_state->name == "Fallecido") {
            $spouses = Spouse::where('affiliate_id', $affiliate->id)->first();
            $beneficiary = $spouses;
            $aid_contributions  = json_decode($request->aid_contributions);
        } else {
            $beneficiary = $affiliate;
            $aid_contributions  = json_decode($request->aid_contributions);
        }
        $pdftitle = "Comprobante";
        $namepdf = Util::getPDFName($pdftitle, $beneficiary);
        // return view('ret_fun.print.beneficiaries_qualification', compact('date','subtitle','username','title','number','retirement_fund','affiliate','submitted_documents'));
        return \PDF::loadView('quota_aid.print.voucher_aid_contribution', 
                compact('date', 
                        'username', 
                        'title',
                        'subtitle', 
                        'affiliate',
                        'beneficiary',
                        'util', 
                        'glosa',
                        'aid_contributions', 
                        'number', 
                        'voucher', 
                        'descripcion', 
                        'payment_date', 
                        'total_literal', 
                        'name_user_complet'))
                ->setPaper('letter')
                ->setOption('encoding', 'utf-8')
                ->setOption('footer-right', 'Pagina [page] de [toPage]')
                ->setOption('footer-left', 'PLATAFORMA VIRTUAL DE LA MUSERPOL - 2018')
                ->stream("$namepdf");
    }
}
