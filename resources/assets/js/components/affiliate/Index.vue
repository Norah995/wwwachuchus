<template>
  <div>
    <!-- Comentado para Produccion  -->
    <!-- <p><code>query: {{ query }}</code></p> -->
    <datatable v-bind="$data">
      <!-- Comentado para Produccion -->
      <!-- <button class="btn btn-default" @click="alertSelectedUids" :disabled="!selection.length">
        <i class="fa fa-commenting-o"></i>
        Alert selected uid(s)
      </button> -->
    </datatable>
  </div>
</template>
<script>
import Vue from 'vue'
import axios from 'axios'
import Datatable from 'vue2-datatable-component'

Vue.use(Datatable) // done!
import mockData from '../_mockData'
import components from './comps/'
export default {
  components,
  name: 'AffiliatesTable', // `name` is required as a recursive component
  props: ['row'], // from the parent FriendsTable (if exists)
  data () {
    const amINestedComp = !!this.row
    return {
      supportBackup: true,
      supportNested: true,
      tblClass: 'table-bordered',
      tblStyle: 'color: #666',
      pageSizeOptions: [5, 10, 15, 20],
      columns: (() => {
        const cols = [
          { title: 'C.I.', field: 'identity_card', thComp: 'FilterTh', tdStyle: { fontStyle: 'italic' } },
          // { title: 'Matrícula', field: 'registration', thComp: 'FilterTh', tdStyle: { fontStyle: 'italic' } },
          { title: 'Grado', field: 'degree', thComp: 'FilterTh', tdStyle: { fontStyle: 'italic' } },
          { title: 'Primer Nombre', field: 'first_name', thComp: 'FilterTh', tdStyle: { fontStyle: 'italic' } },
          { title: 'Segundo Nombre', field: 'second_name', thComp: 'FilterTh', tdStyle: { fontStyle: 'italic' } },
          { title: 'Apellido Paterno', field: 'last_name', thComp: 'FilterTh', tdStyle: { fontStyle: 'italic' } },
          { title: 'Apellido Materno', field: 'mothers_last_name', thComp: 'FilterTh', tdStyle: { fontStyle: 'italic' } },
          { title: 'Apellido de Casada', field: 'surname_husband', thComp: 'FilterTh', tdStyle: { fontStyle: 'italic' } },
          { title: 'Estado', field: 'affiliate_state', thComp: 'FilterTh', tdStyle: { fontStyle: 'italic' } },
           { title: 'Operation', tdComp: 'Opt', visible: 'true' }
        ]
        const groupsDef = {
          Normal: ['Primer Nombre', 'Segundo Nombre', 'Apellido Paterno'],
          Sortable: ['ID'],
          Extra: []
        }
        return cols.map(col => {
          Object.keys(groupsDef).forEach(groupName => {
            if (groupsDef[groupName].includes(col.title)) {
              col.group = groupName
            }            
          })
          return col
        })
      })(),
      data: [],
      total: 0,
      selection: [],
      summary: {},
      // `query` will be initialized to `{ limit: 10, offset: 0, sort: '', order: '' }` by default
      // other query conditions should be either declared explicitly in the following or set with `Vue.set / $vm.$set` manually later
      // otherwise, the new added properties would not be reactive
      query: amINestedComp ? { uid: this.row.friends } : {},
      // any other staff that you want to pass to dynamic components (thComp / tdComp / nested components)
      xprops: {
        eventbus: new Vue()
      }
    }
  },
  watch: {
    query: {
      handler () {
        this.handleQueryChange()
      },
      deep: true
    }
  },
  methods: {
    handleQueryChange () {
      axios.get('/get_all_affiliates',{
        params: this.query
      }).then((response)=> {
        this.data = response.data.affiliates, 
        this.total = response.data.total
        // this.summary = summary
      }).catch(function (error) {
        console.log(error)
      });/*
      mockData(this.query).then(({ rows, total, summary }) => {
        console.log(rows)
        // this.data = rows
      })*/
    },
    alertSelectedUids () {
      console.log(this.selection);
      alert(this.selection.map(({ id }) => id))
    }
  }
}
</script>
<style>
.w-240 {
  width: 240px;
}
</style>
