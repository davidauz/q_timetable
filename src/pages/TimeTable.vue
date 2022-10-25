<template>
	<q-page class='q-pa-lg'>
	<div id='todo-list'>
		<div v-for='n in todos' :key='n.id'>
			<div class='list-item-holder'>
				<div class='row' >
					<div class='col-8' :data-id='n.id' :for='n.id' >{{ n.duedate }}</div>
					<div class='col control-item' @click='item_delt' :data-id='n.id'>🕱</div>
					<div class='col control-item' @click='item_ad1y' :data-id='n.id'>🗓</div>
					<div class='col control-item' @click='item_edit' :data-id='n.id'>✒️</div>
				</div>
				<div class='row' >
					<div  class='col' >{{ n.message }}</div>
				</div>
			</div>
		</div>
		<div id='new-todo-list-item' >
			<q-input rounded outlined 
				v-model="txtNewDate"
				label="Date e.g. 2001-01-01"
				:rules="[ val => val.length >0 || 'Please insert a date']"
			/>
			<q-input rounded outlined 
				v-model="txtNewItem"
				label="Item desc"
				:rules="[ val => val.length >0 || 'Please insert an item desc']"
			/>
			<q-btn v-if='st_status == 1'
				label="Add"
				type="submit"
				color="primary"
				@click="newItem"
			/>
			<q-btn v-if='st_status == 2'
				label="Edit"
				type="edit"
				color="primary"
				@click="edtItem"
			/>
			&nbsp;
			<q-btn 
				label="reset"
				type="reset"
				color="primary"
				@click="formReset"
			/>
		</div>
	</div>
	</q-page>
</template>

<script>
import { defineComponent } from 'vue'
import { ref } from 'vue'
import { api } from 'boot/axios'

function valid_date(dateStr){
	var	s = dateStr.split('-')
	,	d = new Date(+s[2], s[1]-1, +s[0])
	;
	if (Object.prototype.toString.call(d) === "[object Date]")
			return true;
	return false;
}

function findWithAttr(arra, attr, value) {
	for(var i = 0; i < arra.length; i += 1) {
			if(arra[i][attr] == value) {
			return i;
		}
	}
	return -1;
}


export default defineComponent({
	data() {
		return {
			txtNewDate: ref("")
			, txtNewItem: ref("")
			, st_status: ref(1)
			, st_currID: ref(0)
			, todos:[]
		}
	}
 	,async created() {
		try {
			const res = await api.post('ajx_functs.php', {func:'get_list'});
			this.todos = res.data;
		} catch (e) {
			console.error(e);
			alert(e.response.data.messages);
		}
	}
	,
	methods: {
		async item_edit(e) {
			try {
				var findid= e.currentTarget.getAttribute('data-id')
				var index = findWithAttr(this.todos, 'id', findid)
				this.txtNewDate= this.todos[index].duedate
				this.txtNewItem = this.todos[index].message
				this.st_status = 2
				this.st_currID = findid
			} catch (e) {
				console.error(e);
				alert(e.response.data.messages);
			}
		}
		,
		async item_delt(e) {
			try {
				if(false==confirm("Deleting, are you sure?"))
					return;
				var findid= e.currentTarget.getAttribute('data-id');
				await api.post('ajx_functs.php', { func:'delete_item', id:findid }); 
				var index = findWithAttr(this.todos, 'id', findid);
				this.todos.splice(index, 1)
			} catch (e) {
				console.error(e);
				alert(e.response.data.messages);
			}
		}
		,
		async item_ad1y(e) {
			try {
				var findid= e.currentTarget.getAttribute('data-id')
				,	res= await api.post('ajx_functs.php', { func:'item_add_1_year', id:findid })
				;
				this.todos = res.data;
			} catch (e) {
				console.error(e);
				alert(e.response.data.messages);
			}
		}
		,async edtItem() {
			try {
				var	txtNewDate= this.txtNewDate
				;
				if(false==valid_date(txtNewDate))
					return alert_return_false(txtNewDate+' is not a valid date');
				const res = await api.post('ajx_functs.php',
					{	func:'edit_item'
					,	itemid: this.st_currID
					,	newdate:txtNewDate
					,	newitem:this.txtNewItem
					}); 
				this.todos = res.data
				this.txtNewDate = null
				this.txtNewItem = null
				this.st_status = 1
				this.st_currID = 0
			} catch (e) {
				console.error(e);
				alert(e.response.data.messages);
			}
		}
		,async newItem() {
			try {
				var	txtNewDate= this.txtNewDate
				;
				if(false==valid_date(txtNewDate))
					return alert_return_false(txtNewDate+' is not a valid date');
				await api.post('ajx_functs.php', { func:'insert_item', newdate:txtNewDate, newitem:this.txtNewItem }); 
				const res = await api.post('ajx_functs.php', {func:'get_list'});
				this.txtNewDate = null
				this.txtNewItem = null
				this.todos = res.data;
			} catch (e) {
				console.error(e);
				alert(e.response.data.messages);
			}
		}
    	}
})
</script>

<style>
#todo-list {
    border-radius: 14px;
    border: 2px solid #ddd;
}
.list-item-holder {
    padding: 1rem 1rem;
    justify-content: space-between;
    border-bottom: 1px solid #ddd;
}
#new-todo-list-item {
    padding: 1rem;
}
#new-todo-list-item input[type="text"] {
    margin: 0 0 1rem 0;
}
.control-item {
    font-size: 0.875rem;
    background: #eee;
    margin: 0 0 0 0.5rem;
    height: 1.8rem;
    border-radius: 200px;
    transition: all 0.1s ease-out;
    color: rgba(0,0,0,0.5);
    cursor: pointer;
    padding: 0.25rem 0.75rem;
    text-align: center
}
.control-item:hover {
    background: #ddd;
    color: black;
}
label {
    font-weight: 600;
    margin: 5px;
}
</style>
