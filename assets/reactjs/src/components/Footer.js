import React, { Component } from "react";

const defData = ["basic", "story", "reward", "team"];

class Footer extends Component {
	render() {
		const { current, onSet } = this.props;
		const currentIndex = defData.indexOf(current);
		return (
			<div className="row">
				<div className="col-md-12">
					<button
						className={currentIndex == 0 ? "" : "disable"}
						onClick={() => {
							if (currentIndex != 0) {
								onSet(defData[currentIndex-1]);
							}
						}}>Previous</button>
					<button
						className={currentIndex == 3 ? "" : "disable"}
						onClick={() => {
							if (currentIndex != 3) {
								onSet(defData[currentIndex+1]);
							}
						}}>Next</button>
				</div>
			</div>
		);
	}
}
export default Footer;
