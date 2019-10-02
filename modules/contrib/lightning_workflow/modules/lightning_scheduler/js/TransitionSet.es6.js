import { Component, createElement } from 'react';
import dateFormat from 'dateformat';

const t = Drupal.t;

export default class extends Component
{
    /**
     * Constructor.
     *
     * @param {object} props
     * @param {object[]} props.transitions
     * @param {object} props.states
     * @param {string} props.input
     * @param {bool} props.step
     */
    constructor (props)
    {
        super (props);

        const transitions = props.transitions || [];

        const states = Object.entries( props.states );

        this.state = {
            transitions: transitions.map(t => {
                // The date and time of a transition is passed in as an ISO
                // 8601 UTC string, which we need to convert to a date object.
                t.when = new Date(t.when);
                return t;
            }),
        };

        // When creating a new transition, default to the first available
        // moderation state.
        this.defaultState = states[0][0];

        // The moderation state options never change, so build them once now.
        this.stateOptions = states.map(state => {
            const [ id, label ] = state;
            return <option key={ id } value={ id }>{ label }</option>
        });
    }

    /**
     * Renders a saved transition.
     *
     * @param {object} transition
     * @param {string} transition.state
     * @param {Date} transition.when
     * @param {number} index
     * @returns {*}
     */
    renderSaved (transition, index)
    {
        // Render the human-friendly label of the moderation state.
        const state = this.props.states[ transition.state ];
        // Render the date and time in a human-friendly format.
        const date = dateFormat(transition.when, 'longDate');
        const time = dateFormat(transition.when, 'shortTime');

        // Event handler invoked when the transition is removed.
        const onRemove = (event) => {
            event.preventDefault();

            this.setState(prev => {
                // Splice the transition out of the current set.
                prev.transitions.splice(index, 1);
                return prev;
            });
        };

        // Provide a detailed title so we can remove specific transitions in
        // testing.
        const remove_title = t('Remove transition to @state on @date at @time', {
            '@state': state,
            '@date': date,
            '@time': time,
        });

        const class_list = ['scheduled-transition'];

        // TODO: It'd be nice to find a way to make this entire layout translatable.
        return (
            <div className={ class_list.join(' ') }>
                { t('Change to') } <b>{ state }</b> { t('on') } { date } { t('at') } { time }
                &nbsp;<a title={ remove_title } href="#" onClick={ onRemove }>{ t('remove') }</a>
            </div>
        );
    }

    /**
     * Renders the transition edit form.
     *
     * @returns {*}
     */
    renderForm ()
    {
        // TODO: It'd be nice to find a way to make this entire layout translatable.
        return createElement('div', { className: 'scheduled-transition' },
            t('Change to'),
            this.renderStateControl(),
            t('on'),
            this.renderDateControl(),
            t('at'),
            this.renderTimeControl(),
            this.renderFormActions()
        );
    }

    /**
     * Renders the moderation state select box for a transition.
     *
     * @returns {*}
     */
    renderStateControl ()
    {
        const onChange = (event) => {
            // The event target will not be available when updating state, so
            // we need to bind it here.
            const element = event.target;

            this.setState(prev => {
                prev.edit.state = element.options[element.selectedIndex].value;
                return prev;
            });
        };

        const value = this.state.edit.state;

        return (
            <label>
                <span hidden>{ t('Scheduled moderation state') }</span>
                <select defaultValue={ value } onChange={ onChange }>{ this.stateOptions }</select>
            </label>
        );
    }

    /**
     * Renders the date input field for a transition.
     *
     * @returns {*}
     */
    renderDateControl ()
    {
        const onChange = (event) => {
            // The event target will not be available when updating state, so
            // we need to bind it here.
            const element = event.target;

            // Only change the date in state if it's valid.
            if (element.checkValidity())
            {
                this.setState(prev => {
                    const date = element.value.split('-');
                    // The month needs to be zero-based, not one-based.
                    date[1]--;
                    prev.edit.when.setFullYear(...date);
                    return prev;
                });
            }
        };

        const value = dateFormat(this.state.edit.when, 'isoDate');

        return (
            <label>
                <span hidden>{ t('Scheduled transition date') }</span>
                <input required defaultValue={ value } type="date" onChange={ onChange } />
            </label>
        );
    }

    /**
     * Renders the time input field for a transition.
     *
     * @returns {*}
     */
    renderTimeControl ()
    {
        const onChange = (event) => {
            // The event target will not be available when updating state, so
            // we need to bind it here.
            const element = event.target;

            this.setState(prev => {
                prev.edit.when.setHours(...element.value.split(':'));
                return prev;
            });
        };

        let format;

        if (this.props.step >= 3600) {
            format = 'HH:00';
        }
        else if (this.props.step >= 60) {
            format = 'HH:MM';
        }
        else {
            format = 'isoTime';
        }

        const value = dateFormat(this.state.edit.when, format);

        return (
            <label>
                <span hidden>{ t('Scheduled transition time') }</span>
                <input required defaultValue={ value } type="time" onChange={ onChange } step={ this.props.step } />
            </label>
        );
    }

    /**
     * Renders the form actions for a transition being edited.
     *
     * @returns {*[]}
     */
    renderFormActions ()
    {
        // Event handler invoked when the form is cancelled.
        const onCancel = (event) => {
            // Don't actually click the link.
            event.preventDefault();

            this.setState(prev => {
                // The form will not appear if edit is null. See render().
                prev.edit = null;
                return prev;
            });
        };

        // Event handler invoked when the form is saved.
        const onSave = (event) => {
            // Don't actually click the link.
            event.preventDefault();

            this.setState(prev => {
                prev.transitions.push(prev.edit);
                // The form will not appear if edit is null. See render().
                prev.edit = null;
                return prev;
            });
        };

        return (
          <span>
              <button className="button" title={ t('Save transition') } onClick={ onSave }>{ t('Save') }</button>
              &nbsp;{ t('or') }&nbsp;
              <a title={ t('Cancel transition') } href="#" onClick={ onCancel }>{ t('cancel') }</a>
          </span>
        );
    }

    /**
     * Renders the component.
     *
     * @returns {*[]}
     */
    render ()
    {
        const elements = [
            createElement('input', {
                type: 'hidden',
                name: this.props.input,
                value: JSON.stringify(this),
            }),
            this.state.transitions.map((t, i) => this.renderSaved(t, i))
        ];

        // If we're currently editing a transition, render the form. Otherwise,
        // render an 'add another' link.
        elements.push( this.state.edit ? this.renderForm() : this.renderAddLink() );

        return elements;
    }

    toJSON ()
    {
        return this.state.transitions.map(t => {
            return {
              when: t.when.getTime() / 1000,
              state: t.state,
            };
        });
    }

    renderAddLink ()
    {
        // Event handler invoked to add another transition.
        const onAdd = (event) => {
            // Don't actually click the link.
            event.preventDefault();

            this.add();
        };

        const link_text = this.state.transitions.length
            ? t('add another')
            : t('Schedule a status change');

        return <a href="#" onClick={ onAdd }>{ link_text }</a>
    }

    /**
     * Adds a new transition, for editing.
     */
    add ()
    {
        this.setState(prev => {
            prev.edit = {
                state: this.defaultState,
                when: new Date(),
            };

            // If there is an existing transition, use its date and time as
            // the default for one we are creating.
            const last = prev.transitions.slice(-1).shift();

            if (last)
            {
                prev.edit.when.setTime( last.when.getTime() );
            }

            return prev;
        });
    }

}
