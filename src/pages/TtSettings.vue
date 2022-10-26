<template>
	<q-page class='q-pa-lg'>
		<h5>Settings</h5>

		<div class="q-gutter-md" style="max-width: 500px">
			<q-input rounded outlined 
				v-model="txtAddressee" 
				label="Send digest to..."
				:rules="[ val => !!val || '* Required' ]"
			/>

			<q-input rounded outlined 
				v-model="txtServerName" 
				label="SMTP server name/address"
				:rules="[ val => !!val || '* Required' ]"
			/>

			<q-input rounded outlined 
				v-model.number="txtServerPort"
				type="number"
				label="SMTP server port"
				:rules="
				[ val => !!val || '* Required' ] "
			/>

			<q-input rounded outlined 
				v-model="txtUserName"
				label="User for authentication"
				:rules="[ val => !!val || '* Required' ]"
			/>

			<q-input  rounded outlined 
				v-model="txtPwd1" filled
				:type="isPwd ? 'password' : 'text'" 
				hint="Password"
				:rules="[ val => !!val || '* Required' ]"
			>
				<template v-slot:append>
					<q-icon
					:name="isPwd ? 'visibility_off' : 'visibility'"
					class="cursor-pointer"
					@click="isPwd = !isPwd"
					/>
				</template>
			</q-input>

			<q-input  rounded outlined
				v-model="txtPwd2" 
				filled :type="isPwd ? 'password' : 'text'" 
				hint="Repeat Password"
				:rules="[ val => !!val || '* Required' ]"
			>
				<template v-slot:append>
					<q-icon
					:name="isPwd ? 'visibility_off' : 'visibility'"
					class="cursor-pointer"
					@click="isPwd = !isPwd"
					/>
				</template>
			</q-input>


			<q-select rounded outlined v-model="slcServerSecurity" :options="secOptions" label="Server security" />

			<div v-if="errors.length">
				<div class=error_title>Please correct the following error(s):</div>
				<p v-for="error in errors" :key="error">{{ error }}</p>
			</div>
			<div v-if="successMsg.length">
				{{ successMsg }}
			</div>

			<div class='reasonable-padding'>
				<q-btn label="Submit" type="submit" @click='onSubmit' color="primary"/>
				&nbsp;
				<q-btn label="Test connection" type="submit" color="primary" @click='test_connection'/>
				&nbsp;
				<q-btn label="Reset" type="reset" color="primary" flat class="q-ml-sm" />
			</div>
		</div>
	</q-page>
</template>

<script>
import { defineComponent } from 'vue'
import { ref } from 'vue'
import { api } from 'boot/axios'

export default defineComponent({
	data() {
		return {
			isPwd: ref(true)
			, txtServerName: ref("")
			, txtAddressee: ref("")
			, txtServerPort: ref("")
			, txtUserName: ref("")
			, txtPwd1: ref("")
			, txtPwd2: ref("")
			, slcServerSecurity: ref("")
			, secOptions: [ 'None', 'STARTTLS', 'SSL/TLS' ]
			, errors:[]
			, successMsg: ""
		}
	}
    	, async created() {
		try {
			const res = await api.post('ajx_functs.php', {func:'get_cfg'});
			var datas=res.data
			,	key
			,	val
			;
			for (var i =0; i<datas.length; i++) {
				key=datas[i].key
				val=datas[i].value
				switch(key) {
					case 'txtAddressee':
						this.txtAddressee=val
						break
					case 'txtServerName':
						this.txtServerName=val
						break
					case 'txtServerPort':
						this.txtServerPort=val
						break
					case 'txtUserName':
						this.txtUserName=val
						break
					case 'txtPwd1':
						this.txtPwd1=val
						this.txtPwd2=val
						break
					case 'slcServerSecurity':
						this.slcServerSecurity=val
						break
					default:
console.log(key);
				}
			}
		} catch (e) {
			console.error(e);
			alert(e.response.data.messages);
		}
	}
	, setup(){
	}
	, methods: {
		async onSubmit(){
			var datas=this.$data
			;
			this.errors = [];
			this.successMsg= "Saving..."
			if
			(	null==datas.txtAddressee
			||	0==datas.txtAddressee.length
			)
				this.errors.push('Please insert a valid addressee.');
			if
			(	null==datas.txtPwd1
			||	0==datas.txtPwd1.length
			)
				this.errors.push('Please insert a password.');

			if
			(	null==datas.txtUserName
			||	0==datas.txtUserName.length
			)
				this.errors.push('Please insert a valid User Name.');

			if
			(	null==datas.txtServerName
			||	0==datas.txtServerName.length
			)
				this.errors.push('Please insert a valid server name/address.');

			if
			(	null==datas.txtServerPort
			||	0==datas.txtServerPort
			||	isNaN(datas.txtServerPort)
			)
				this.errors.push('Please insert a valid port number.');
			
			if( datas.txtPwd2 != datas.txtPwd1)
				this.errors.push('Password verification failed.  The two passwords do not match.');

			if (this.errors.length)
				return true;
			await api.post('ajx_functs.php', { func:'save_cfg', datas }); 
			this.successMsg= "Configuration saved"
		}
		, async test_connection(){
			this.successMsg= "Sending test email..."
try {
			await api.post('ajx_functs.php', { func:'test_connection' }); 
}catch (e) {
console.error(e);
alert(e.response.data.messages);
}
			this.successMsg= "Connection success!"
		}
		, onReset(){}
	}
})
</script>

<style>
.reasonable-padding {
    padding: 1rem;
}
</style>
