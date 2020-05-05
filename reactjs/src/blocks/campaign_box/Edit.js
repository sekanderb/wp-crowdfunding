import { isUndefined, pickBy } from 'lodash';

const { __ } = wp.i18n;
const { apiFetch } = wp;
const { withSelect } = wp.data
const { addQueryArgs } = wp.url;
const { withState } = wp.compose;
const { InspectorControls } = wp.editor;
const { Component, Fragment } = wp.element;
const { PanelBody,SelectControl, RangeControl, TextControl, Spinner, QueryControls } = wp.components;
 
class Edit extends Component {

    constructor() {
		super( ...arguments );
    }
    

    render() {
        const {
            attributes: {
                numbers, 
                campaignID
            },
            setAttributes,
        } = this.props

        const { products } = this.props

		const blockSettings = (
            <PanelBody title={ __( 'Query Product', 'wp-crowdfunding' ) } initialOpen={ true }>
                <QueryControls
                    numberOfItems={ numbers }
                    type="TextControl"
                    onNumberOfItemsChange={ numbers => setAttributes( { numbers } ) }
                />
                
            </PanelBody>
		);

        // Campaign ID 
        const CampaignIDControl = withState( {
            campaignID: campaignID,
        } )( ( { campaignID, setState } ) => ( 
            <TextControl
                label={ __( 'Campaign ID' ) }
                value={ campaignID }
                onChange={ ( value ) => setAttributes( { campaignID: value } ) }
                type="number"
            />
        ) );


        let output = '';

        return (
            <Fragment>
                <InspectorControls key="inspector">
                    {blockSettings}
                    <CampaignIDControl />
                </InspectorControls>

                <div className="wpneo-wrapper">
                    <div className="wpneo-container">
                        { (products && products.length) ?
                        <Fragment>
                        { products &&
                            <div className="wpneo-wrapper-inner">
                                { products.map(product => {

                                    output = <div className={`wpneo-listings two col-${product.column}`}>
                                        <div className="wpneo-listing-img">
                                            
                                            <div dangerouslySetInnerHTML={{__html: product.wpcf_product.product_thumb}} />
                                            
                                            <div className="overlay">
                                                <div>
                                                    <div>
                                                        <a href="#">View Campaign</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="wpneo-listing-content">
                                            <div className="woocommerce"></div>
                                            <h4><a href="">{product.title.rendered}</a></h4>
                                            <p className="wpneo-author">by
                                                <a href=""> {product.wpcf_product.display_name}</a>
                                            </p>
                                            <div className="wpneo-location">
                                                <i className="wpneo-icon wpneo-icon-location"></i>
                                                <div className="wpneo-meta-desc">{product.wpcf_product.location}</div>
                                            </div>
                                            <p className="wpneo-short-description">{product.wpcf_product.desc}</p>
                                            <div className="wpneo-raised-percent">
                                                <div className="wpneo-meta-name">Raised Percent :</div>
                                                <div className="wpneo-meta-desc">{product.wpcf_product.raised_percent}</div>
                                            </div>
                                                                                        
                                            <div className="wpneo-raised-bar">
                                                <div id="neo-progressbar">
                                                    <div style={{width: product.wpcf_product.raised_percent}}></div>
                                                </div>
                                            </div>

                                            <div className="wpneo-funding-data">
                                                <div className="wpneo-funding-goal">
                                                    <div className="wpneo-meta-desc"><span className="woocommerce-Price-amount amount"><span className="woocommerce-Price-currencySymbol">$</span>{product.wpcf_product.funding_goal}</span>
                                                    </div>
                                                    <div className="wpneo-meta-name">Funding Goal</div>
                                                </div>
                                                <div className="wpneo-time-remaining">
                                                    <div className="wpneo-meta-desc">{product.wpcf_product.days_remaining}</div>
                                                    <div className="wpneo-meta-name float-left">Days to go</div>
                                                </div>
                                                <div className="wpneo-fund-raised">
                                                    <div className="wpneo-meta-desc">
                                                        <span className="woocommerce-Price-amount amount">
                                                        <span className="woocommerce-Price-currencySymbol">$</span>{product.wpcf_product.total_raised}</span>
                                                    </div>
                                                    <div className="wpneo-meta-name">Fund Raised</div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    return output

                                })} 
                            </div>
                        }
                        </Fragment>
                        :
                        <div className="wpcf-products-is-loading">
                            <Spinner />
                        </div>
                        }
                    </div>
                </div>

            </Fragment>
        )
    }
}


export default withSelect((select, props) => {
    const { attributes: { numbers, campaignID } } = props
    const { getEntityRecords } = select( 'core' );

    const output = ({products: getEntityRecords('postType', 'product', { per_page: 1, status: 'publish', _embed: true, } )})

    return output; 
})

(Edit)

