<template>
<div class="row">
    <div class="col-md-12">
        <div class="col-lg-12">
            <div class="panel panel-primary"  :class="show_spinner ? 'sk-loading' : ''" >
                <div class="panel-heading">
                    <h3 class="pull-left">Pago de Aportes</h3>
                    <div class="text-right">
                        <button data-animation="flip" class="btn btn-primary" @click="PrintQuote()"><i class="fa fa-print" ></i> </button>
                    </div>
                </div>

                <div class="panel-body" id ="print">  
                    <div class="sk-folding-cube" v-show="show_spinner">
                        <div class="sk-cube1 sk-cube"></div>
                        <div class="sk-cube2 sk-cube"></div>
                        <div class="sk-cube4 sk-cube"></div>
                        <div class="sk-cube3 sk-cube"></div>
                    </div>
                    <div class="row" >
                        
                        <div class="col-md-6" style="margin-bottom:20px">
                            <label>Tipo de Aporte:</label>
                            <select v-model="tipo" class="form-control" v-on:change="changeType">
                                <option value="2">Comisión</option>
                                <option value="10">Agregado Policial</option>
                                <option value="9">Baja Temporal</option>
                           </select>
                            <span v-show="errors.has('tipo')" class="text-danger">{{ errors.first('tipo') }}</span>
                        </div>
                        <div class="col-md-3" >
                            <label>Repetir sueldo:</label>
                            <input type="text" class="form-control"  data-money='true' @keyup.enter="repeatSalary" v-model="general_salary" >                            
                        </div>
                    </div>
                    <table class="table table-striped" data-page-size="15">
                        <thead>
                        <tr>
                            <th class="footable-visible footable-first-column footable-sortable">Mes/Año<span class="footable-sort-indicator"></span></th>
                            <th data-hide="phone" class="footable-visible footable-sortable">Total Ganado Bs.<span class="footable-sort-indicator"></span></th>
                            <th data-hide="phone" class="footable-visible footable-sortable">F.R.P. (4.77 %)<span class="footable-sort-indicator"></span></th>
                            <th data-hide="phone" class="footable-visible footable-sortable">Cuota Mortuoria (1.09 %)<span class="footable-sort-indicator"></span></th>
                            <th data-hide="phone" class="footable-visible footable-sortable">Ajuste UFV Bs.<span class="footable-sort-indicator"></span></th>
                            <th data-hide="phone,tablet" class="footable-visible footable-sortable">Subtotal Aporte<span class="footable-sort-indicator"></span></th>
                            <th>Opciones</th>                                    
                        </tr>
                        </thead>
                        <tbody>
                            <tr style="" v-for="(con, index) in contributions" :key="index" id="form">
                                <td>                                    
                                    <input type="text"  v-model="con.monthyear" disabled class="form-control">
                                </td>
                                <td>
                                    <input type="text" v-model = "con.sueldo" data-money="true" @keyup.enter="CalcularAporte(con, index)"  ref="s1"  class="form-control" >
                                </td>
                                <td>
                                    <input type="text"  v-model = "con.fr" data-money='true' disabled class="form-control">
                                </td>
                                <td>
                                    <input type="text" v-model = "con.cm" data-money="true" disabled class="form-control">
                                </td>
                                <td>
                                    <input type="text" v-model = "con.interes" disabled data-money="true" class="form-control">
                                </td>
                                <td>
                                    <input type="text"  v-model = "con.subtotal" data-money="true" disabled class="form-control">
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-circle" @click="RemoveRow(index)" type="button"><i class="fa fa-times"></i>  </button>
                                </td>
                                
                            </tr>
                            <tr>
                                <td colspan="2"><label for="total">Total a Pagar por Concepto de Aportes:</label></td>
                                <td colspan="3"><input type="text" v-model="total" data-money="true" disabled class="form-control"></td>
                                <!--<td> <button class="btn btn-success btn-circle" onClick="window.location.reload()" type="button"><i class="fa fa-link"></i></button></td>-->
                            </tr>                            
                        </tbody>
                    </table>
                    <button class="btn btn-primary " type="button" :disabled="!disabledSaved" @click="Guardar()"><i class="fa fa-save"></i>&nbsp;Guardar</button>

                </div>
               
            </div>
        </div>
    </div>
</div> 
</template>
<script>
import {
        dateInputMask,
        moneyInputMask,
        parseMoney,
        moneyInputMaskAll
    }
    from "../../helper.js";
export default {
    
    props: [
        'contributions1',
        'afid',
        'last_quotable',
        'rate',
    ],
    data() {   

    return {
      contributions: [],
      total:0,
      tipo:null,
      ufv:0,
      estado: true,
      afi_id:null,
      show_spinner:false,
      count:3,
      ufvs: [],
      general_salary: 0,
    };
  },
   
  mounted() {
    this.contributions = this.contributions1;  
    this.afi_id = this.afid;    
    //alert('making time');    
    window.addEventListener("load", function(event) {
        moneyInputMaskAll();
    });        
  },
  created(){    
  },
  methods: {      
      RemoveRow(index) {
        this.contributions.splice(index,1);
        this.SumTotal();
      },
      Refresh() {
        this.contributions = this.contributions1;
      },
      repeatSalary(){
          var i;
        for(i=0;i<this.contributions.length;i++){
            this.contributions[i].sueldo = this.general_salary;
            this.CalcularAporte(this.contributions[i],i);
        }              
      },
      CalcularAporte(con, index){        
        con.sueldo = parseMoney(con.sueldo);   
        if(parseFloat(con.sueldo) >0)
        {            
        if(this.count > 0)
        {
            this.show_spinner=true
            if(this.ufvs[con.sueldo] && false)
            {                           
                con.fr = con.sueldo * this.rate.retirement_fund/100;
                con.cm = con.sueldo * this.rate.mortuary_quota/100;
                con.interes = parseFloat(this.ufv);
                con.subtotal =  (con.fr + con.cm + con.interes).toFixed(2);                
            
                this.show_spinner=false;

                this.SumTotal();
            }
            else
            {                
            axios.post('/get-interest',{con})
            .then(response => {                
                this.ufv = response.data.replace(',','.');                
                con.interes = parseFloat(this.ufv);
                this.ufvs[con.sueldo] = this.ufv;
                var newfr,newcm;
                newfr = (con.sueldo * this.rate.retirement_fund/100);
                con.fr = newfr.toFixed(2);
                newcm = (con.sueldo * this.rate.mortuary_quota/100);
                con.cm = newcm.toFixed(2);
                con.subtotal =  (newfr + newcm + con.interes).toFixed(2);                
                this.show_spinner=false;                                          
                this.SumTotal();
                this.count = 3;
                if(index +1 < this.contributions.length)
                this.$refs.s1[index +1].focus();    
            })
            .catch(e => {
                
                console.log(--this.count);
                console.log("40004");
                
                this.show_spinner=false;
                this.CalcularAporte(con, index);
            })}
        }
        else
        {
            this.show_spinner=false;
            this.count = 3;
            return;
        }
        }
          
      },

      changeType:function(e){
          var i;
          if(e.target.value == 9){              
              for(i=0;i<this.contributions.length && this.last_quotable!=0;i++){
                this.contributions[i].sueldo = this.last_quotable;                
                this.CalcularAporte(this.contributions[i],i);
              }
          }    
          else {
              for(i=0;i<this.contributions.length;i++){
                this.contributions[i].sueldo = 0;
                this.contributions[i].fr = 0;
                this.contributions[i].cm = 0;
                this.contributions[i].interes = 0;
                this.contributions[i].subtotal = 0;
              }
          }
      },

      SumTotal(){
            let total1 = 0;
            this.contributions.forEach(con => {                            
                total1 += parseFloat(con.subtotal) ;                
           });
        this.total = total1.toFixed(2);

      },
      PrintQuote(){                              
          this.contributions =  this.contributions.filter((item)=> {
            return (item.sueldo != 0 && item.fr != 0 && item.cm !=0 && item.subtotal != 0);
        });
        var contributions = this.contributions;
        var con = JSON.stringify(contributions);
        var affiliate_id = this.afid;
        var total = this.total;        
        printJS({printable:'/print_contributions_quote?contributions='+con+'&affiliate_id='+affiliate_id+'&total='+total, type:'pdf', showModal:true});
      },
      setDataToTable(period,amount){                    
        $('#main'+period).html(amount);
      },
      enableDC(){
          $(".directContribution").removeClass('disableddiv');
      },
      Guardar(){                
        if(this.tipo !== null) 
        {
            this.contributions =  this.contributions.filter((item)=> {                
                return (item.sueldo != 0 && item.fr != 0 && item.cm !=0 && item.subtotal != 0);
            });       
      
            if(this.contributions.length > 0)
            {   
                this.$swal({
                title: 'Esta usted seguro de guardar?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar'
                }).then((result) => {    
                    if (result.value) {                    
                    var aportes = this.contributions;                    
                    axios.post('/contribution_save',{aportes,total:this.total,tipo:this.tipo,afid:this.afid})
                    .then(response => {                  
                    this.enableDC();
                    var i;
                    for(i=0;i<response.data.contribution.length;i++){                        
                        this.setDataToTable(response.data.contribution[i].month_year,response.data.contribution[i].total);
                    }
                    this.$swal({
                    title: 'Pago realizado',
                    showConfirmButton: false,
                    timer: 6000,
                    type: 'success'
                    })
                    var json_contribution= JSON.stringify(response.data.contributions);                    
                    printJS({printable:
                            '/ret_fun/'+
                            response.data.affiliate_id+
                            '/print/voucher/'+
                            response.data.voucher_id + "?contributions="+json_contribution, 
                            type:'pdf', showModal:true});
                    })                    
                    .catch(error => {
                    this.show_spinner = false;                                    
                        console.log(error.response.data);
//                        console.log(xhr.responseText);
//                        var resp = jQuery.parseJSON(xhr.responseText);
                        var resp = error.response.data;
                        $.each(resp, function(index, value)
                        {
                            flash(value,'error',6000);
                        });                        
                    })
                }
                })            
            }
        } 
    },
  },
  computed: {
      disabledSaved(){
       return this.contributions.some((c)=> c.subtotal > 0 );
      }
  }
}
</script>

