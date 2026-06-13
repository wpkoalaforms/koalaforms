<template>
  <div class="kf-form-row kf-form-row-address">
    <!-- Only render top-level blocks -->
    <div v-for="(block, index) in config.innerBlocks" :key="index" 
         :style="extractStyle(block.innerHTML)" :class="extractClass(block.innerHTML)">
      <!-- Use Columns only for core/columns, Column for leaf nodes -->
      <Columns v-if="block.blockName === 'core/columns'" :config="block" :form-data="blockData" @validate="validate" :channel="channel"/>
      <Column v-else :config="block" :form-data="blockData" @validate="validate" :channel="channel"/>
    </div>
  </div>
</template>
<script src="./js/address.js"></script>
