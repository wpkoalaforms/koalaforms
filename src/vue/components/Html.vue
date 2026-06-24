<template>
  <!-- Conditionally render the label if inputLabel is provided -->
   <div v-html="config.innerHTML"></div>

   <div v-if="isDisclosure">
      <input 
         type="checkbox"
         :id="uniqueId"
         :name="config.attrs.name"
         :value="modelValue"
         :checked="modelValue"
         @input="handleUpdate">

      <label v-if="config.attrs.checkLabel" :for="uniqueId">{{ config.attrs.checkLabel }}</label>
   </div>
</template>

<script src="./js/html.js"></script>
