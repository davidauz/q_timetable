<template>
  <q-layout view="lHh Lpr lFf">
    <q-header>
      <q-toolbar>
        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="toggleLeftDrawer"
        />

        <q-toolbar-title>
          Timetable
        </q-toolbar-title>

        <div>{{today_date}}</div>
	<q-img
    	src='~assets/ThorsHelmet.jpg'
	class='header-image absolute-top'
	/>
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
    >
<q-list>

<q-item-label header >
Sections
</q-item-label>

<q-item to='/' clickable v-ripple >
	<q-item-section avatar >
		<q-icon name="list" />
	</q-item-section>
	Main
</q-item>

<q-item to='config' clickable v-ripple >
	<q-item-section avatar >
		<q-icon name="settings" />
	</q-item-section>
	Settings
</q-item>

<q-item to='reports' clickable v-ripple >
	<q-item-section avatar >
		<q-icon name="work" />
	</q-item-section>
	Reports
</q-item>

      </q-list>
    </q-drawer>


	<q-page-container>
		<router-view v-slot="{ Component }">
			<keep-alive>
				<component :is="Component" />
			</keep-alive>
		</router-view>
	</q-page-container>
  </q-layout>
</template>

<script>
import { date } from 'quasar'
import { defineComponent, ref } from 'vue'

export default defineComponent({
  name: 'MainLayout',

  components: {
  },
  computed:{
  	today_date(){
		const timeStamp = Date.now()
		return date.formatDate(timeStamp, 'YYYY-MM-DD')
	}
  },
  setup () {
    const leftDrawerOpen = ref(false)

    return {
      leftDrawerOpen,
      toggleLeftDrawer () {
        leftDrawerOpen.value = !leftDrawerOpen.value
      }
    }
  }
})
</script>

<style lang='scss'>
.header-image{
height: 100%;
z-index:-1;
}
</style>
