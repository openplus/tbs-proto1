import { createElement } from 'react';
import { render } from 'react-dom';

import TransitionSet from './TransitionSet.es6';

const transitionSets = document.getElementsByTagName('TransitionSet');

for (let i = 0; i < transitionSets.length; i++) {
    const transitionSet = transitionSets[i];
    const dataElement = transitionSet.querySelector('input[ type="hidden" ]');

    const element = createElement(TransitionSet, {
        states: JSON.parse(
            transitionSet.getAttribute('states')
        ),
        transitions: JSON.parse(
            dataElement.value,
        ),
        input:
            dataElement.name,
        step:
            transitionSet.getAttribute('step') || 60,
    });
    render(element, transitionSet);
}
