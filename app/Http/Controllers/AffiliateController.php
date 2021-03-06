<?php

namespace Muserpol\Http\Controllers;

use Muserpol\Models\Affiliate;
use Muserpol\Models\AffiliateState;
use Muserpol\Models\Category;
use Muserpol\Models\City;
use Muserpol\Models\Degree;
use Muserpol\Models\PensionEntity;
use Muserpol\Models\Contribution\Contribution;
use Illuminate\Http\Request;
use Log;
use Yajra\Datatables\Datatables;
use Muserpol\Models\RetirementFund\RetirementFund;
use Muserpol\Models\QuotaAidMortuary\QuotaAidMortuary;
use Muserpol\Models\AffiliateRecord;

class AffiliateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {                
        return view('affiliates.index');
    }
    public function getAllAffiliates(Request $request)
    {
        /*$query = Affiliate::take(100)->get();

        return $datatables->collection($query)
                          // ->addColumn('action', 'eloquent.tables.users-action')
                          ->make(true);*/
        $offset = $request->offset ?? 0;
        $limit = $request->limit ?? 10;
        $sort = $request->sort ?? 'id';
        $order = $request->order ?? 'asc';  
        $last_name = strtoupper($request->last_name) ?? '';
        $first_name = strtoupper($request->first_name) ?? '';
        $second_name = strtoupper($request->second_name) ?? '';
        $mothers_last_name = strtoupper($request->mothers_last_name) ?? '';
        $surname_husband = strtoupper($request->surname_husband) ?? '';
        $identity_card = strtoupper($request->identity_card) ?? '';
        //$total=Affiliate::where('identity_card','LIKE',$identity_card.'%')->where('last_name','LIKE',$last_name.'%')->count();        
        //$total=6669783;
        //$affiliates = Affiliate::skip($offset)->take($limit)->orderBy($sort,$order)->where('last_name','LIKE',$last_name.'%')->get();                

        $total = Affiliate::select('affiliates.id')//,'identity_card','registration','degrees.name as degree','first_name','second_name','last_name','mothers_last_name','civil_status')->
                                ->leftJoin('degrees', 'affiliates.id', '=', 'degrees.id')
                                ->leftJoin('affiliate_states', 'affiliates.affiliate_state_id', '=', 'affiliate_states.id')
                                ->whereRaw("coalesce(affiliates.first_name,'' ) LIKE '$first_name%'")
                                ->whereRaw("coalesce(affiliates.second_name,'' ) LIKE '$second_name%'")
                                ->whereRaw("coalesce(affiliates.last_name,'') LIKE '$last_name%'")
                                ->whereRaw("coalesce(affiliates.mothers_last_name,'') LIKE '$mothers_last_name%'")
                                ->whereRaw("coalesce(affiliates.surname_husband,'') LIKE '$surname_husband%'")
                                ->whereRaw("coalesce(affiliates.identity_card, '') LIKE '$identity_card%'")
                                ->count();

        $affiliates = Affiliate::select(
            'affiliates.id',
            'identity_card',
            'registration',
            'first_name',
            'second_name',
            'surname_husband',
            'last_name',
            'mothers_last_name',
            'degrees.name as degree',
            'civil_status',
            'affiliate_states.name as affiliate_state'
        )
                                ->leftJoin('degrees','affiliates.id','=','degrees.id')
                                ->leftJoin('affiliate_states','affiliates.affiliate_state_id','=','affiliate_states.id')
                                ->skip($offset)
                                ->take($limit)
                                ->orderBy($sort,$order)
                                ->whereRaw("coalesce(affiliates.first_name,'' ) LIKE '$first_name%'")
                                ->whereRaw("coalesce(affiliates.second_name,'' ) LIKE '$second_name%'")
                                ->whereRaw("coalesce(affiliates.last_name,'') LIKE '$last_name%'")
                                ->whereRaw("coalesce(affiliates.mothers_last_name,'') LIKE '$mothers_last_name%'")
                                ->whereRaw("coalesce(affiliates.surname_husband,'') LIKE '$surname_husband%'")
                                ->whereRaw("coalesce(affiliates.identity_card, '') LIKE '$identity_card%'")
                                ->get();
        return response()->json(['affiliates' => $affiliates->toArray(),'total'=>$total]);
    }

    /** 
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \Muserpol\Affiliate  $affiliate
     * @return \Illuminate\Http\Response
     */
    public function show(Affiliate $affiliate)
    {
        $this->authorize('view',$affiliate);
        $cities = City::all()->pluck('first_shortened', 'id');
        $birth_cities = City::all()->pluck('name', 'id');
        $categories = Category::all()->pluck('name', 'id');
        $degrees = Degree::all()->pluck('name', 'id');
        $pension_entities = PensionEntity::all()->pluck('name', 'id');
        $affiliate_states = AffiliateState::all()->pluck('name', 'id');
        $affiliate_records = AffiliateRecord::where('affiliate_id', $affiliate->id)
        ->orderBy('id','desc')
        ->get();
        // $quota_mortuaries = QuotaAidMortuary::where('affiliate_id', $affiliate->id)->get();
        /*$records_message=[];
        foreach($affiliate_records as $key=>$affiliate_record){
            $records_message[$key]=substr($affiliate_record->message,0,-20);
        }*/
        //return $records_message;
        $quota_mortuaries = [];
        $cuota = null;
        $auxilio = null;
            foreach($quota_mortuaries as $quota_mortuary){
                if($quota_mortuary->procedure_modality->procedure_type->module_id == 4){
                   $cuota = $quota_mortuary;
                }
                if($quota_mortuary->procedure_modality->procedure_type->module_id == 5){
                      $auxilio = $quota_mortuary;
                    }
            }
        
        $retirement_fund = RetirementFund::where('affiliate_id', $affiliate->id)->first();
        $nextcode = RetirementFund::where('affiliate_id', $affiliate->id)->where('code','LIKE','%A')->first();
        if(isset($nextcode))
            $nextcode = $nextcode->code;
        else 
            $nextcode = "";        
        $affiliate->load([
            'city_identity_card:id,first_shortened',
            'city_birth:id,name',
            'affiliate_state',
            'pension_entity',
            'category',
            'degree',
        ]);
        $data = array(
            'retirement_fund'=>$retirement_fund,
            'affiliate'=>$affiliate,
            'cities'=>$cities,
            'birth_cities'=>$birth_cities,
            'categories'=>$categories,
            'degrees'=>$degrees,
            'pension_entities' =>$pension_entities,
            'affiliate_states'=>$affiliate_states, 
            'cuota'=>$cuota,
            'auxilio'=>$auxilio,
            'affiliate_records'=>$affiliate_records,
            'nextcode'  =>  $nextcode,
            //'records_message'=>$records_message
        );
        return view('affiliates.show')->with($data);
        //return view('affiliates.show',compact('affiliate','affiliate_states', 'cities', 'categories', 'degrees','degrees_all', 'pension_entities','retirement_fund'));
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Muserpol\Affiliate  $affiliate
     * @return \Illuminate\Http\Response
     */
    public function edit(Affiliate $affiliate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Muserpol\Affiliate  $affiliate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Affiliate $affiliate)
    { 
        $affiliate = Affiliate::where('id','=', $affiliate->id)->first();

        $this->authorize('update', $affiliate);

        $affiliate->identity_card = $request->identity_card;
        $affiliate->first_name = $request->first_name;
        $affiliate->second_name = $request->second_name;
        $affiliate->last_name = $request->last_name;
        $affiliate->mothers_last_name = $request->mothers_last_name;
        $affiliate->gender = $request->gender;
        $affiliate->civil_status = $request->civil_status;
        $affiliate->birth_date = $request->birth_date;
        $affiliate->phone_number = $request->phone_number;
        $affiliate->cell_phone_number = $request->cell_phone_number;
        $affiliate->city_birth_id = $request->city_birth_id;
        $affiliate->city_identity_card_id =$request->city_identity_card_id;
        $affiliate->surname_husband = $request->surname_husband;

        $affiliate->save();

        $datos=array('affiliate' => $affiliate ,'city_birth' => $affiliate->city_birth,'city_identity_card' => $affiliate->city_identity_card);
        return $datos;

    }
    public function update_affiliate_police(Request $request, Affiliate $affiliate)
    {
        $affiliate = Affiliate::where('id','=', $affiliate->id)->first();
        $this->authorize('update', $affiliate);
        $affiliate->affiliate_state_id = $request->affiliate_state_id;
        $affiliate->type = $request->type;
        $affiliate->date_entry = $request->date_entry;
        $affiliate->item = $request->item;
        $affiliate->category_id = $request->category_id;
        $affiliate->degree_id = $request->degree_id;
        $affiliate->pension_entity_id = $request->pension_entity_id;
        $affiliate->save();
        
        $datos = array('affiliate'=>$affiliate,'state'=>$affiliate->affiliate_state,'category'=>$affiliate->category,'degree'=>$affiliate->degree,'pension_entity'=>$affiliate->pension_entity);
        return $datos;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Muserpol\Affiliate  $affiliate
     * @return \Illuminate\Http\Response
     */
    public function destroy(Affiliate $affiliate)
    {
        //
        $this->authorize('delete', $affiliate);
    }
}
