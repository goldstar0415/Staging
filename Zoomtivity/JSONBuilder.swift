//
//  JSONBuilder.swift
//  Zoomtivity
//
//  Created by Callum Trounce on 17/02/2017.
//  Copyright © 2017 Zoomtivity. All rights reserved.
//

import Foundation
import CoreLocation

class JSONBuilder : NSObject {
    

    
    static func buildJSONForPOIRequest(type: String!,  southWestPoint: CLLocationCoordinate2D!, northEastPoint: CLLocationCoordinate2D!) {
        
        let southWest: [String : AnyObject] = [
            "lat" : southWestPoint.latitude as AnyObject,
            "lng" : southWestPoint.longitude as AnyObject
        ]
        
        let northEast: [String : AnyObject] = [
            "lat" : northEastPoint.latitude as AnyObject,
            "lng" : northEastPoint.longitude as AnyObject
        ]
        
        let boundBoxes: [String: AnyObject] = [
            "_southWest" : southWest as AnyObject,
            "_northEast" : northEast as AnyObject
        ]
        
        let filter: [String: AnyObject] = [
            "rating" : 0 as AnyObject,
            "tags" : [] as AnyObject,
            "b_boxes" : boundBoxes as AnyObject
        ]
        
        
        let jsonObject: [String: AnyObject] = [
            "search_text" : "" as AnyObject,
            "type" : "food" as AnyObject,
            "filter" : filter as AnyObject
            
        ]
        
        let valid = JSONSerialization.isValidJSONObject(jsonObject)
        print(valid)
    }
    
}
