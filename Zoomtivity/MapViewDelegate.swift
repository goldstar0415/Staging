//
//  MapViewDelegate.swift
//  Zoomtivity
//
//  Created by Callum Trounce on 18/02/2017.
//  Copyright © 2017 Zoomtivity. All rights reserved.
//

import Foundation
import SKMaps

extension MapViewController : SKMapViewDelegate {
    
    func mapView(_ mapView: SKMapView, didSelect annotation: SKAnnotation) {
        print(annotation.location)
    }
    
}
