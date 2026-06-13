<!-- 
      This is the form element.
      Purpose of this element is to host the form HTML structure and 
      load the child elements using a slot.
-->
<template>
  <div class="form-container">
    
    <form @submit.prevent="handleSubmit" class="kf-form-design-matchmytheme" data-design="matchmytheme" data-style="label_top" novalidate>
      <!-- The slot where other elements (e.g., form fields) will be inserted -->
      <slot></slot>
    </form>
  </div>
</template>

<script> export default { name: 'Form' }; </script>
