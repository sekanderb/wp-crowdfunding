import { combineReducers } from 'redux';
import user from './user';
import myCampaign from './myCampaign';
import investedCampaign from './investedCampaign';
import bookmarkCampaign from './bookmarkCampaign';
import pledgeReceived from './pledgeReceived';
import order from './order';
import withdraw from './withdraw';

const rootReducer = combineReducers({
    user,
    myCampaign,
    investedCampaign,
    bookmarkCampaign,
    pledgeReceived,
    order,
    withdraw,
});

export default rootReducer;