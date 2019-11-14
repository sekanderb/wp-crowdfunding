import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { required, getYotubeVideoID, uploadFiles, removeArrValue, multiIndex } from '../Helper';
import { FormSection, Field, FieldArray, reduxForm, getFormValues, change as changeFieldValue } from 'redux-form';
import { fetchSubCategories, fieldShowHide } from '../actions';
import RenderRepeatableFields from './fields/Repeatable';
import RenderField from './fields/Single';
import PreviewBasic from './preview/Basic';
import PreviewEmpty from './preview/Empty';
import PreviewLink from './preview/Link';
import PageControl from './Control';

const formName = "campaignForm";
const sectionName = "basic";
class Basic extends Component {
    constructor(props) {
        super(props);
        this.state = { sectionActive: 0, requiredFields: {} };
        this._onBlurVideoLink = this._onBlurVideoLink.bind(this);
        this._onChangeRange = this._onChangeRange.bind(this);
        this._removeArrValue = this._removeArrValue.bind(this);
        this._uploadFile = this._uploadFile.bind(this);
        this._addTag = this._addTag.bind(this);
    }

    componentDidMount() {
        const { formValues: {basic} } =  this.props;
        if( basic.category ) {
            this.props.fetchSubCategories(basic.category);
        }
        if( basic.goal_type ) {
            this.updateFieldOption();
        }
    }

    componentDidUpdate(prevProps) {
        const { formValues: {basic: curVal} } =  this.props;
        const { formValues: {basic: prevVal} } = prevProps;
        if( curVal.category && curVal.category !== prevVal.category) {
            const sub_cat = `${sectionName}.sub_category`;
            this.props.changeFieldValue(formName, sub_cat, null);
            this.props.fetchSubCategories(curVal.category);
        }
        if( curVal.goal_type && curVal.goal_type !== prevVal.goal_type) {
            this.updateFieldOption();
        }
    }

    updateFieldOption() {
        const { formValues: {basic} } =  this.props;
        const field = 'media.if_target_date';
        const show = (basic.goal_type=='target_date') ? true : false;
        this.props.fieldShowHide(field, show);
        this.forceUpdate();
    }

    _onBlurVideoLink() {
        const files = [];
        const type = 'video_link';
        const { formValues: {basic: {video_link} } } =  this.props;
        video_link.map( i => {
            const id = getYotubeVideoID(i.src);
            if(id) {
                const item = {
                    id: id,
                    type: type,
                    src: i.src,
                    thumb: `https://img.youtube.com/vi/${id}/default.jpg`,
                };
                files.push(item);
            }
        });
        setTimeout(() => {
            this.addMediaFile(files);
            this.removeMediaFile(type, files);
        }, 300)
    }

    _onChangeRange(e) {
        const { formValues } = this.props;
        const { name, value, attributes } = e.target;
        const range = attributes.getNamedItem('range').value;
        let inputValue = parseInt(value) || 0;
        let compareRange = true;
        if(range=='min' || range=='max') {
            const nameArray = name.split('.');
            const rangeValue = multiIndex(formValues, nameArray);
            compareRange = (range=='min') ? inputValue<rangeValue['max'] : inputValue>rangeValue['min'];
            inputValue = Object.assign({}, rangeValue, {[range]: inputValue});
        }
        if(compareRange) {
            this.props.changeFieldValue(formName, name, inputValue);
        }
    }

    _removeArrValue(type, index, field, values, is_media) {
        values = removeArrValue(values, index);
        this.props.changeFieldValue(formName, field, values);
        if( is_media ) {
            this.removeMediaFile(type, values);
        }
    }

    _uploadFile(type, field, sFiles, multiple, is_media) {
        uploadFiles(type, sFiles, multiple).then( (files) => {
            this.props.changeFieldValue(formName, field, files);
            if( is_media ) {
                this.addMediaFile(files);
                this.removeMediaFile(type, files);
            }
        });
    }

    _addTag(tag, field, selectedTags) {
        selectedTags = [...selectedTags];
        if( selectedTags.findIndex( item => item.value == tag.value) === -1 ) {
            selectedTags.push(tag);
            this.props.changeFieldValue(formName, field, [...selectedTags]);
        }
    }

    addMediaFile(files) {
        const { formValues: { basic }, changeFieldValue } =  this.props;
        let media = [ ...basic.media ];
        files.map((item) => {
            const index = media.findIndex(i => i.id === item.id);
            if(index === -1) {
                media.push(item); //add files in media store which is not added before
            }
        });
        changeFieldValue(formName, `${sectionName}.media`, media);
    }

    removeMediaFile(type, files) {
        const { formValues: { basic }, changeFieldValue } =  this.props;
        let media = [ ...basic.media ];
        media.filter((item) => item.type === type).map((item, i) => {
            const index = files.findIndex(i => i.id === item.id);
            if(index === -1) {
                const mIndex = media.findIndex(i => i.id === item.id);
                media.splice(mIndex, 1); //remove files from media store which is unselected
            }
        });
        changeFieldValue(formName, `${sectionName}.media`, media);
    }

    render() {
        const { sectionActive } = this.state;
        const { postId, totalRaised, totalBackers, fields, formValues, handleSubmit, current, prevStep, lastStep } =  this.props;
        const basicValues = (formValues && formValues.hasOwnProperty(sectionName)) ? formValues[sectionName] : {};

        return (
            <div className="row">
                <div className='col-md-7'>
                    <form onSubmit={handleSubmit}>
                        <FormSection name={sectionName}>
                            <div className='wpcf-accordion-wrapper'>
                                {Object.keys(fields).map( (section, index) =>
                                    <div key={section} className='wpcf-accordion'>
                                        <div tabIndex={0} className={`wpcf-accordion-title ${index == sectionActive ? 'active' : ''}`} onClick={ () => this.setState({sectionActive:index}) }>
                                            <span className="fas fa-check"></span>{ section.replace('_', ' ') }
                                        </div>
                                        <div className='wpcf-accordion-details' style={{ display: (index==sectionActive) ? 'block' : 'none' }}>
                                            {Object.keys(fields[section]).map( key => {
                                                const field = fields[section][key];
                                                const validate = field.required ? [required] : [];
                                                if(field.show) {
                                                    return (
                                                        <div key={key} className='wpcf-form-group'>
                                                            <label className='wpcf-field-title'>{field.title}</label>
                                                            <p className='wpcf-field-desc'>{field.desc}</p>

                                                            { field.type == 'form_group' ?
                                                                <div className="form-group">
                                                                    {Object.keys(field.fields).map( key => {
                                                                        const gField = field.fields[key];
                                                                        const gValidate = gField.required ? [required] : [];
                                                                        return (
                                                                            <Field
                                                                                key={key}
                                                                                name={key}
                                                                                item={gField}
                                                                                fieldValue={basicValues[key] || ''}
                                                                                validate={gValidate}
                                                                                component={RenderField}/>
                                                                            )
                                                                    })}
                                                                </div>

                                                            : field.type == 'repeatable' ?
                                                                <FieldArray
                                                                    name={key}
                                                                    item={field}
                                                                    onBlurVideoLink={this._onBlurVideoLink}
                                                                    component={RenderRepeatableFields}/>

                                                            :   <Field
                                                                    name={key}
                                                                    item={field}
                                                                    addTag={this._addTag}
                                                                    onChangeRange={this._onChangeRange}
                                                                    uploadFile={this._uploadFile}
                                                                    removeArrValue={this._removeArrValue}
                                                                    fieldValue={basicValues[key] || ''}
                                                                    validate={validate}
                                                                    component={RenderField}/>
                                                            }
                                                        </div>
                                                    )
                                                }
                                            })}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </FormSection>

                        <PageControl
                            current={current}
                            prevStep={prevStep}
                            lastStep={lastStep}/>
                    </form>
                </div>
				<div className='col-md-5'>
                    <div className='wpcf-form-sidebar'>
                        <div className="preview-title"><span className="fas fa-eye"></span> Preview</div>
                        {sectionActive==2 && basicValues.media.length ? 
                            <PreviewBasic
                                data={basicValues}
                                raised={totalRaised}
                                backers={totalBackers}/>
                        :
                            <PreviewEmpty/>
                        }
                        <PreviewLink postId={postId}/>
                    </div>
                </div>
            </div>
        )
    }
}

const mapStateToProps = state => ({
    postId: state.data.postId,
    fields: state.data.basic_fields,
    totalRaised: state.data.totalRaised,
    totalBackers: state.data.totalBackers,
    formValues: getFormValues(formName)(state)
});

const mapDispatchToProps = dispatch => {
    return bindActionCreators({
        getFormValues,
        changeFieldValue,
        fetchSubCategories,
        fieldShowHide
    }, dispatch);
}

export default connect(mapStateToProps, mapDispatchToProps)(reduxForm({
    form: formName,
    destroyOnUnmount: false, //preserve form data
  	forceUnregisterOnUnmount: true, //unregister fields on unmount
})(Basic));
