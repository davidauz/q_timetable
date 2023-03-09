<!--
This file is the root component of a Vue.js application, aka the entry point.
This is a "single file" component, it has 3 sections: template, script, and style.

template: HTML markup for the component.

script: JavaScript code to define behavior and logic of the component.
  For properties, methods, and lifecycle hooks

style: CSS for the component, can include both scoped and global styles, 
  can use any CSS pre-processor
-->
<template>
<router-view />
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'App'
})
</script>
<!--
The script section contains the JavaScript code that defines the behavior and logic of the component.
The component has properties, methods, and lifecycle hooks.
Properties are data values that can be used in the template section of the component to render dynamic content.
'data' property: 
  Properties that are defined as objects inside the component
  example: a 'message' property with value 'XXX'
	data() {
		return {
			message: 'XXX'
		}
	}
'methods' property:
  functions that can be called from the template or other JavaScript code
  example: a "greet" function
	methods: {
		greet() {
			alert(this.message);
		}
	}
  The 'methods' property also has lifecycle hooks, i.e. methods that are called at
  specific points during the lifecycle of the component, defined as
  methods with predefined names such as 'mounted', 'updated', and
  'beforeDestroy'.
-->
