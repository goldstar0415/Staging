//
//  APIManager.swift
//  Zoomtivity
//
//  Created by Callum Trounce on 17/02/2017.
//  Copyright © 2017 Zoomtivity. All rights reserved.
//

import Foundation
import Alamofire
import CoreLocation
import SwiftyJSON

class APIManager : NSObject {
    
    typealias APIResponseHandler = ([[String:AnyObject]]) -> Void
    
    static let sharedAPIManager = APIManager()
    
    func fetchPointsOfInterest(type: String!,  southWestPoint: CLLocationCoordinate2D!, northEastPoint:CLLocationCoordinate2D!, completion: @escaping APIResponseHandler) {
        
        let body = JSONBuilder.buildJSONForPOIRequest(type: type,
                                                      southWestPoint: southWestPoint,
                                                      northEastPoint: northEastPoint)
        

        Alamofire.request("https://test-front-george.zoomtivity.com/api/map/spots",
                          method: .post ,
                          parameters: body,
                          encoding: JSONEncoding.default ,
                          headers: nil).response { (response) in
                    
                            if let error = response.error {
                                completion([["error" : error.localizedDescription as AnyObject]])
                            }
                            
                            if let data = response.data {
                                
                                let swiftyJsonVar = JSON(data: data)
                                completion(swiftyJsonVar.arrayObject as! [[String:AnyObject]])
                             
                            }
                            
        }
        
    }
    
}
