import VariableNumber from './fieldtypes/VariableNumber.vue';
import VariableNumberIndex from './fieldtypes/VariableNumberIndex.vue';

Statamic.booting(() => {
    Statamic.$components.register('variable_number-fieldtype', VariableNumber);
    Statamic.$components.register('variable_number-fieldtype-index', VariableNumberIndex);
});
